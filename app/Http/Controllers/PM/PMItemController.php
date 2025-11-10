<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\TemporaryUploadAssociate;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\User;
use App\Models\Location;
use App\Models\SmsSent;
use App\Models\Receipt;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class PMItemController extends Controller
{
    public function pending(Request $request)
    {
        Log::info('PMItemController@pending accessed by user', [
            'user_id' => Auth::id(),
            'user_role' => Auth::guard('pm')->user()->role
        ]);

        $currentUser = Auth::guard('pm')->user();
        $searchTerm = $request->get('search');

        // Get pending items for the current PM's location
        $query = TemporaryUploadAssociate::with([
                'temporaryUpload.user',
                'temporaryUpload.location'
            ])
            ->where('status', 'pending')
            ->whereHas('temporaryUpload', function ($query) use ($currentUser) {
                $query->where('location_id', $currentUser->location_id);
            });

        // Add search functionality
        if ($searchTerm) {
            $query->whereHas('temporaryUpload.user', function ($q) use ($searchTerm) {
                $q->where('nic', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $pendingItems = $query->orderBy('created_at', 'desc')->paginate(20);

        // Preserve search parameter in pagination
        if ($searchTerm) {
            $pendingItems->appends(['search' => $searchTerm]);
        }

        return view('pm.items.pending', compact('pendingItems'));
    }

    public function pendingByServiceType(Request $request, $serviceType)
    {
        Log::info('PMItemController@pendingByServiceType accessed', [
            'user_id' => Auth::id(),
            'service_type' => $serviceType
        ]);

        // Validate service type
        $validServiceTypes = ['register_post', 'slp_courier', 'cod', 'remittance'];
        if (!in_array($serviceType, $validServiceTypes)) {
            abort(404, 'Invalid service type');
        }

        $currentUser = Auth::guard('pm')->user();
        $searchTerm = $request->get('search');

        // Get pending items filtered by service type from TemporaryUploadAssociate table
        $query = TemporaryUploadAssociate::with([
                'temporaryUpload.user',
                'temporaryUpload.location'
            ])
            ->where('status', 'pending')
            ->where('service_type', $serviceType)
            ->whereHas('temporaryUpload', function ($query) use ($currentUser) {
                $query->where('location_id', $currentUser->location_id);
            });

        // Add search functionality
        if ($searchTerm) {
            $query->whereHas('temporaryUpload.user', function ($q) use ($searchTerm) {
                $q->where('nic', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('name', 'LIKE', '%' . $searchTerm . '%')
                  ->orWhere('email', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $pendingItems = $query->orderBy('created_at', 'desc')->paginate(20);

        // Preserve search parameter in pagination
        if ($searchTerm) {
            $pendingItems->appends(['search' => $searchTerm]);
        }

        // Get service type label for display
        $serviceTypeLabels = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD',
            'remittance' => 'Remittance'
        ];

        $serviceTypeLabel = $serviceTypeLabels[$serviceType];

        return view('pm.items.pending', compact('pendingItems', 'serviceType', 'serviceTypeLabel'));
    }

    public function accept(Request $request, $id)
    {
        Log::info('PMItemController@accept called', [
            'user_id' => Auth::id(),
            'item_id' => $id
        ]);

        $item = TemporaryUploadAssociate::findOrFail($id);

        // Verify this item belongs to the PM's location
        $currentUser = Auth::guard('pm')->user();
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            abort(403, 'Unauthorized access to this item.');
        }

        DB::beginTransaction();
        try {
            // Always handle as individual item acceptance
            // Even if part of temporary_list, accept only this specific item
            return $this->acceptSingleItemFromAnyCategory($item, $currentUser);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error accepting item: ' . $e->getMessage());
            return back()->with('error', 'Error accepting item: ' . $e->getMessage());
        }
    }

    private function acceptSingleItem($item, $currentUser)
    {
        // PM must provide barcode before acceptance - no auto-generation
        $barcode = $item->barcode;
        if (!$barcode) {
            return back()->with('error', 'Barcode is required. Please add a barcode first before accepting this item.');
        }

        // Create ItemBulk record first
        $itemBulk = ItemBulk::create([
            'sender_name' => $item->temporaryUpload->user->name,
            'service_type' => $item->service_type ?? 'register_post',
            'location_id' => $item->temporaryUpload->location_id,
            'created_by' => $currentUser->id,
            'category' => 'single_item',
            'item_quantity' => 1,
        ]);

        // Create Item record from temporary data
        $newItem = Item::create([
            'item_bulk_id' => $itemBulk->id,
            'barcode' => $barcode,
            'receiver_name' => $item->receiver_name,
            'receiver_address' => $item->receiver_address,
            'status' => 'accept',
            'weight' => $item->weight,
            'amount' => $item->amount ?? 0,
            'created_by' => $item->temporaryUpload->user_id, // Original customer
            'updated_by' => $currentUser->id, // PM who accepted
        ]);

        // Log SMS notification for acceptance
        SmsSent::create([
            'item_id' => $newItem->id,
            'sender_mobile' => $item->temporaryUpload->user->mobile ?? '',
            'receiver_mobile' => $item->contact_number ?? '',
            'status' => 'accept',
        ]);

        // Create Receipt for the accepted item (include both amount and postage)
        $codAmount = $item->amount ?? 0; // COD amount from temp upload
        $postageAmount = $item->postage ?? 0; // Postage from temp upload

        // Total calculation based on service type:
        // - COD: postage + amount
        // - SLP Courier/Register Post: postage only
        $totalAmount = $codAmount + $postageAmount;

        $receipt = Receipt::create([
            'item_quantity' => 1,
            'item_bulk_id' => $itemBulk->id,
            'amount' => $codAmount, // COD amount only
            'postage' => $postageAmount, // Postage fees
            'total_amount' => $totalAmount, // Combined total
            'payment_type' => 'cash',
            'created_by' => $currentUser->id,
            'location_id' => $item->temporaryUpload->location_id,
            'passcode' => $this->generatePasscode()
        ]);

        // Update temporary record status
        $item->status = 'accept';
        $item->save();

        DB::commit();

        return back()->with('success', 'Item accepted successfully and moved to final system. Barcode: ' . $barcode);
    }

    private function acceptSingleItemFromAnyCategory($item, $currentUser)
    {
        // PM must provide barcode before acceptance - no auto-generation
        $barcode = $item->barcode;
        if (!$barcode) {
            return back()->with('error', 'Barcode is required. Please add a barcode first before accepting this item.');
        }

        // For items from temporary_list, we need to check if an ItemBulk already exists
        // If not, create one. If yes, use the existing one.
        $temporaryUpload = $item->temporaryUpload;

        if ($temporaryUpload->category === 'temporary_list') {
            // ALWAYS create new ItemBulk for customer upload acceptance
            // This ensures proper sequential ItemBulk IDs and prevents reuse of old records
            $itemBulk = ItemBulk::create([
                'sender_name' => $temporaryUpload->user->name,
                'service_type' => $item->service_type ?? 'register_post',
                'location_id' => $temporaryUpload->location_id,
                'created_by' => $currentUser->id,
                'category' => 'temporary_list',
                'item_quantity' => 1,
            ]);
        } else {
            // Single item - create individual ItemBulk
            $itemBulk = ItemBulk::create([
                'sender_name' => $temporaryUpload->user->name,
                'service_type' => $item->service_type ?? 'register_post',
                'location_id' => $temporaryUpload->location_id,
                'created_by' => $currentUser->id,
                'category' => 'single_item',
                'item_quantity' => 1,
            ]);
        }

        // Create Item record from temporary data
        $newItem = Item::create([
            'item_bulk_id' => $itemBulk->id,
            'barcode' => $barcode,
            'receiver_name' => $item->receiver_name,
            'receiver_address' => $item->receiver_address,
            'status' => 'accept',
            'weight' => $item->weight,
            'amount' => $item->amount ?? 0,
            'created_by' => $temporaryUpload->user_id, // Original customer
            'updated_by' => $currentUser->id, // PM who accepted
        ]);

        // Create Payment record for COD items
        if ($item->service_type === 'cod' && ($item->amount ?? 0) > 0) {
            Payment::create([
                'item_id' => $newItem->id,
                'fixed_amount' => $item->amount,
                'commission' => $item->commission ?? 0.00,
                'item_value' => $item->item_value ?? $item->amount,
                'status' => 'accept',
            ]);
        }

        // Log SMS notification for acceptance
        SmsSent::create([
            'item_id' => $newItem->id,
            'sender_mobile' => $temporaryUpload->user->mobile ?? '',
            'receiver_mobile' => $item->contact_number ?? '',
            'status' => 'accept'
        ]);

        // Create or update receipt based on category
        if ($temporaryUpload->category === 'single_item') {
            // For single items, create individual receipt with item amount only (no postage)
            Receipt::create([
                'item_quantity' => 1,
                'item_bulk_id' => $itemBulk->id,
                'amount' => $item->amount ?? 0, // Only item amount, no postage
                'payment_type' => 'cash',
                'created_by' => $currentUser->id,
                'location_id' => $temporaryUpload->location_id,
                'passcode' => $this->generatePasscode()
            ]);
        } else {
            // For temporary_list (bulk), find existing receipt or create new one
            $existingReceipt = Receipt::where('item_bulk_id', $itemBulk->id)->first();

            if ($existingReceipt) {
                // Update existing receipt with new quantity and amount
                $existingReceipt->item_quantity = $itemBulk->item_quantity;
                $existingReceipt->amount += ($item->amount ?? 0); // Add only item amount
                $existingReceipt->save();
            } else {
                // Create new receipt for bulk
                Receipt::create([
                    'item_quantity' => $itemBulk->item_quantity,
                    'item_bulk_id' => $itemBulk->id,
                    'amount' => $item->amount ?? 0, // Only item amount, no postage
                    'payment_type' => 'cash',
                    'created_by' => $currentUser->id,
                    'location_id' => $temporaryUpload->location_id,
                    'passcode' => $this->generatePasscode()
                ]);
            }
        }

        // Update temporary item status to accepted
        $item->update(['status' => 'accept']);

        DB::commit();

        return back()->with('success', 'Individual item accepted successfully and moved to final system. Barcode: ' . $barcode);
    }

    private function acceptBulkUpload($temporaryUpload, $currentUser)
    {
        // Get all pending items from this bulk upload
        $pendingItems = $temporaryUpload->associates()->where('status', 'pending')->get();

        if ($pendingItems->isEmpty()) {
            DB::rollback();
            return back()->with('error', 'No pending items found in this bulk upload.');
        }

        // Create a new ItemBulk record for this temporary upload
        // Each temporary upload gets its own ItemBulk record with category 'temporary_list'
        $itemBulk = ItemBulk::create([
            'sender_name' => $temporaryUpload->user->name,
            'service_type' => $pendingItems->first()->service_type ?? 'register_post',
            'location_id' => $temporaryUpload->location_id,
            'created_by' => $currentUser->id,
            'category' => 'temporary_list',
            'item_quantity' => $pendingItems->count(),
        ]);

        $acceptedCount = 0;
        $barcodes = [];

        // Check that all items have barcodes before accepting
        $itemsWithoutBarcode = [];
        foreach ($pendingItems as $item) {
            if (!$item->barcode) {
                $itemsWithoutBarcode[] = "Item ID: {$item->id} (Receiver: {$item->receiver_name})";
            }
        }

        if (!empty($itemsWithoutBarcode)) {
            DB::rollback();
            $missingList = implode(', ', $itemsWithoutBarcode);
            return back()->with('error', "Cannot accept bulk upload. The following items are missing barcodes: {$missingList}. Please add barcodes to all items first.");
        }

        // Accept all pending items in the bulk upload
        foreach ($pendingItems as $item) {
            $barcode = $item->barcode; // Barcode is guaranteed to exist at this point
            $barcodes[] = $barcode;

            // Create Item record from temporary data
            $newItem = Item::create([
                'item_bulk_id' => $itemBulk->id,
                'barcode' => $barcode,
                'receiver_name' => $item->receiver_name,
                'receiver_address' => $item->receiver_address,
                'status' => 'accept',
                'weight' => $item->weight,
                'amount' => $item->amount ?? 0,
                'created_by' => $temporaryUpload->user_id, // Original customer
                'updated_by' => $currentUser->id, // PM who accepted
            ]);

            // Log SMS notification for acceptance
            SmsSent::create([
                'item_id' => $newItem->id,
                'sender_mobile' => $temporaryUpload->user->mobile ?? '',
                'receiver_mobile' => $item->contact_number ?? '',
                'status' => 'accept',
            ]);

            // Update temporary record status
            $item->status = 'accept';
            $item->save();

            $acceptedCount++;
        }

        // Create a single receipt for the entire bulk upload
        // Calculate total amount (only item amounts, no postage)
        $totalBulkAmount = $pendingItems->sum(function($item) {
            return $item->amount ?? 0;
        });

        $receipt = Receipt::create([
            'item_quantity' => $acceptedCount,
            'item_bulk_id' => $itemBulk->id,
            'amount' => $totalBulkAmount, // Only item amounts, no postage
            'payment_type' => 'cash',
            'created_by' => $currentUser->id,
            'location_id' => $temporaryUpload->location_id,
            'passcode' => $this->generatePasscode()
        ]);

        DB::commit();

        return back()->with('success', "Bulk upload accepted successfully! {$acceptedCount} items moved to final system. ItemBulk ID: {$itemBulk->id}");
    }

    public function reject(Request $request, $id)
    {
        Log::info('PMItemController@reject called', [
            'user_id' => Auth::id(),
            'item_id' => $id
        ]);

        $item = TemporaryUploadAssociate::findOrFail($id);

        // Verify this item belongs to the PM's location
        $currentUser = Auth::guard('pm')->user();
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            abort(403, 'Unauthorized access to this item.');
        }

        $item->status = 'reject';
        $item->save();

        return back()->with('success', 'Item rejected successfully.');
    }

    public function updateBarcode(Request $request, $id)
    {
        Log::info('PMItemController@updateBarcode called', [
            'user_id' => Auth::id(),
            'item_id' => $id,
            'barcode' => $request->barcode
        ]);

        // Validate the barcode
        $request->validate([
            'barcode' => 'required|string|max:255'
        ]);

        $item = TemporaryUploadAssociate::findOrFail($id);

        // Verify this item belongs to the PM's location
        $currentUser = Auth::guard('pm')->user();
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this item.'
            ], 403);
        }

        // Check if barcode already exists in temporary uploads
        $existingBarcode = TemporaryUploadAssociate::where('barcode', $request->barcode)
            ->where('id', '!=', $id)
            ->first();

        if ($existingBarcode) {
            return response()->json([
                'success' => false,
                'message' => 'This barcode is already in use by another item.'
            ]);
        }

        // Check if barcode exists in the main items table
        $existingItem = Item::where('barcode', $request->barcode)->first();
        if ($existingItem) {
            return response()->json([
                'success' => false,
                'message' => 'This barcode is already in use in the main system.'
            ]);
        }

        // Update the barcode
        $item->barcode = $request->barcode;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Barcode updated successfully.',
            'barcode' => $request->barcode
        ]);
    }

    /**
     * Update temporary item data only - NO acceptance, NO database insertion
     * After update, user must use Accept buttons in list view to process to database
     */
    public function updateOnly(Request $request, $id)
    {
        Log::info('PMItemController@updateOnly called', [
            'user_id' => Auth::id(),
            'item_id' => $id,
            'weight' => $request->weight,
            'barcode' => $request->barcode
        ]);

        // Validate the request
        $rules = [
            'weight' => 'required|numeric|min:0.01',
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'contact_number' => 'nullable|string|max:15',
            'amount' => 'required|numeric|min:0',
            'barcode' => 'required|string|max:255'
        ];

        // Only validate item_value for COD services
        $item = TemporaryUploadAssociate::findOrFail($id);
        if ($item->service_type === 'cod') {
            $rules['item_value'] = 'required|numeric|min:0';
        } else {
            $rules['item_value'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        // Verify this item belongs to the PM's location
        $currentUser = Auth::guard('pm')->user();
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            return back()->with('error', 'Unauthorized access to this item.');
        }

        DB::beginTransaction();
        try {
            // Update all editable fields in temporary_upload_associates table ONLY
            $item->weight = $request->weight;
            $item->receiver_name = $request->receiver_name;
            $item->receiver_address = $request->receiver_address;
            $item->contact_number = $request->contact_number;
            $item->amount = $request->amount;

            // Only update item_value for COD services, set to 0 for others
            if ($item->service_type === 'cod') {
                $item->item_value = $request->item_value ?? 0;
            } else {
                $item->item_value = 0;
            }

            // Only update barcode if it's different (in case PM set it earlier)
            if ($item->barcode !== $request->barcode) {
                // Check barcode uniqueness in temporary table
                $existingBarcode = TemporaryUploadAssociate::where('barcode', $request->barcode)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingBarcode) {
                    DB::rollback();
                    return back()->with('error', 'This barcode is already in use by another item.');
                }

                // Check barcode uniqueness in main items table
                $existingItem = Item::where('barcode', $request->barcode)->first();
                if ($existingItem) {
                    DB::rollback();
                    return back()->with('error', 'This barcode is already in use in the main system.');
                }

                $item->barcode = $request->barcode;
            }

            // ONLY save to temporary table - NO acceptance, NO database insertion
            $item->save();

            DB::commit();

            // Redirect back to the customer upload list view where user can use Accept buttons
            return redirect()->route('pm.view-customer-upload', $item->temporary_id)
                ->with('success', 'Item details updated successfully! Use the Accept buttons below to process to database.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating item', [
                'error' => $e->getMessage(),
                'item_id' => $id,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Error updating item: ' . $e->getMessage());
        }
    }

    public function acceptWithUpdates(Request $request, $id)
    {
        Log::info('PMItemController@acceptWithUpdates called', [
            'user_id' => Auth::id(),
            'item_id' => $id,
            'weight' => $request->weight,
            'barcode' => $request->barcode
        ]);

        // Validate the request
        $rules = [
            'weight' => 'required|numeric|min:0.01',
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'contact_number' => 'nullable|string|max:15',
            'amount' => 'required|numeric|min:0',
            'barcode' => 'required|string|max:255'
        ];

        // Only validate item_value for COD services
        $item = TemporaryUploadAssociate::findOrFail($id);
        if ($item->service_type === 'cod') {
            $rules['item_value'] = 'required|numeric|min:0';
        } else {
            $rules['item_value'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        // Verify this item belongs to the PM's location
        $currentUser = Auth::guard('pm')->user();
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            return back()->with('error', 'Unauthorized access to this item.');
        }

        DB::beginTransaction();
        try {
            // Update all editable fields
            $item->weight = $request->weight;
            $item->receiver_name = $request->receiver_name;
            $item->receiver_address = $request->receiver_address;
            $item->contact_number = $request->contact_number;
            $item->amount = $request->amount;

            // Only update item_value for COD services, set to 0 for others
            if ($item->service_type === 'cod') {
                $item->item_value = $request->item_value ?? 0;
            } else {
                $item->item_value = 0;
            }

            // Only update barcode if it's different (in case PM set it earlier)
            if ($item->barcode !== $request->barcode) {
                // Check barcode uniqueness in temporary table
                $existingBarcode = TemporaryUploadAssociate::where('barcode', $request->barcode)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingBarcode) {
                    DB::rollback();
                    return back()->with('error', 'This barcode is already in use by another item.');
                }

                // Check barcode uniqueness in main items table
                $existingItem = Item::where('barcode', $request->barcode)->first();
                if ($existingItem) {
                    DB::rollback();
                    return back()->with('error', 'This barcode is already in use in the main system.');
                }

                $item->barcode = $request->barcode;
            }

            $item->save();

            // Accept this individual item regardless of category
            $result = $this->acceptSingleItemFromAnyCategory($item, $currentUser);

            DB::commit();

            return back()->with('success', 'Item accepted successfully with updated details!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error accepting item with updates', [
                'error' => $e->getMessage(),
                'item_id' => $id,
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Error accepting item: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error accepting item: ' . $e->getMessage()
            ]);
        }
    }

    public function edit($id)
    {
        Log::info('PMItemController@edit accessed', [
            'user_id' => Auth::id(),
            'item_id' => $id
        ]);

        $item = TemporaryUploadAssociate::with(['temporaryUpload.user', 'temporaryUpload.location'])
            ->findOrFail($id);

        // Verify this item belongs to the PM's location
        $currentUser = Auth::guard('pm')->user();
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            abort(403, 'Unauthorized access to this item.');
        }

        // Get service type labels for display
        $serviceTypeLabels = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD'
        ];

        return view('pm.items.edit', compact('item', 'serviceTypeLabels'));
    }

    public function quickAccept($id)
    {
        Log::info('PMItemController@quickAccept called', [
            'user_id' => Auth::id(),
            'item_id' => $id
        ]);

        $currentUser = Auth::guard('pm')->user();
        $item = TemporaryUploadAssociate::with(['temporaryUpload.user', 'temporaryUpload.location'])
            ->findOrFail($id);

        // Verify this item belongs to the PM's location
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this item.'
            ], 403);
        }

        // Check if already processed
        if ($item->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Item is not in pending status.'
            ]);
        }

        try {
            DB::beginTransaction();

            // Check if this is part of a bulk upload (temporary_list)
            $temporaryUpload = $item->temporaryUpload;

            if ($temporaryUpload->category === 'temporary_list') {
                // Handle bulk upload quick acceptance
                return $this->quickAcceptBulkUpload($temporaryUpload, $currentUser);
            } else {
                // Handle single item quick acceptance
                return $this->quickAcceptSingleItem($item, $currentUser);
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error quick accepting item: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error accepting item: ' . $e->getMessage()
            ]);
        }
    }

    private function quickAcceptSingleItem($item, $currentUser)
    {
        // Generate barcode for the new item
        $barcode = $item->barcode ?: 'ITM' . time() . str_pad($item->id, 4, '0', STR_PAD_LEFT);

        // Create ItemBulk record first
        $itemBulk = ItemBulk::create([
            'sender_name' => $item->temporaryUpload->user->name,
            'service_type' => $item->service_type ?? 'register_post',
            'location_id' => $item->temporaryUpload->location_id,
            'created_by' => $currentUser->id,
            'category' => 'single_item',
            'item_quantity' => 1,
        ]);

        // Create Item record from temporary data
        $newItem = Item::create([
            'item_bulk_id' => $itemBulk->id,
            'barcode' => $barcode,
            'receiver_name' => $item->receiver_name,
            'receiver_address' => $item->receiver_address,
            'status' => 'accept',
            'weight' => $item->weight,
            'amount' => $item->amount ?? 0,
            'created_by' => $item->temporaryUpload->user_id, // Original customer
            'updated_by' => $currentUser->id, // PM who accepted
        ]);

        // Log SMS notification for acceptance
        SmsSent::create([
            'item_id' => $newItem->id,
            'sender_mobile' => $item->temporaryUpload->user->mobile ?? '',
            'receiver_mobile' => $item->contact_number ?? '',
            'status' => 'accept',
        ]);

        // Update temporary record status
        $item->status = 'accept';
        $item->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Item accepted successfully! Barcode: ' . $barcode
        ]);
    }

    private function quickAcceptBulkUpload($temporaryUpload, $currentUser)
    {
        // Get all pending items from this bulk upload
        $pendingItems = $temporaryUpload->associates()->where('status', 'pending')->get();

        if ($pendingItems->isEmpty()) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'No pending items found in this bulk upload.'
            ]);
        }

        // Create a new ItemBulk record for this temporary upload
        // Each temporary upload gets its own ItemBulk record with category 'temporary_list'
        $itemBulk = ItemBulk::create([
            'sender_name' => $temporaryUpload->user->name,
            'service_type' => $pendingItems->first()->service_type ?? 'register_post',
            'location_id' => $temporaryUpload->location_id,
            'created_by' => $currentUser->id,
            'category' => 'temporary_list',
            'item_quantity' => $pendingItems->count(),
        ]);

        $acceptedCount = 0;
        $barcodes = [];

        // Accept all pending items in the bulk upload
        foreach ($pendingItems as $item) {
            // Generate barcode for each item
            $barcode = $item->barcode ?: 'BLK' . time() . str_pad($item->id, 4, '0', STR_PAD_LEFT);
            $barcodes[] = $barcode;

            // Create Item record from temporary data
            $newItem = Item::create([
                'item_bulk_id' => $itemBulk->id,
                'barcode' => $barcode,
                'receiver_name' => $item->receiver_name,
                'receiver_address' => $item->receiver_address,
                'status' => 'accept',
                'weight' => $item->weight,
                'amount' => $item->amount ?? 0,
                'created_by' => $temporaryUpload->user_id, // Original customer
                'updated_by' => $currentUser->id, // PM who accepted
            ]);

            // Log SMS notification for acceptance
            SmsSent::create([
                'item_id' => $newItem->id,
                'sender_mobile' => $temporaryUpload->user->mobile ?? '',
                'receiver_mobile' => $item->contact_number ?? '',
                'status' => 'accept',
            ]);

            // Update temporary record status
            $item->status = 'accept';
            $item->save();

            $acceptedCount++;
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Bulk upload accepted successfully! {$acceptedCount} items moved to final system. ItemBulk ID: {$itemBulk->id}"
        ]);
    }

    /**
     * Accept entire bulk upload at once
     */
    public function acceptBulkUploadCompletely($temporaryUploadId)
    {
        Log::info('PMItemController@acceptBulkUploadCompletely called', [
            'user_id' => Auth::id(),
            'temporary_upload_id' => $temporaryUploadId
        ]);

        $currentUser = Auth::guard('pm')->user();
        $temporaryUpload = \App\Models\TemporaryUpload::with(['user', 'location', 'associates'])
            ->findOrFail($temporaryUploadId);

        // Verify this upload belongs to the PM's location
        if ($temporaryUpload->location_id !== $currentUser->location_id) {
            return back()->with('error', 'Unauthorized access to this bulk upload.');
        }

        // Only allow accepting temporary_list uploads
        if ($temporaryUpload->category !== 'temporary_list') {
            return back()->with('error', 'This method only works for bulk uploads.');
        }

        DB::beginTransaction();
        try {
            // Get all pending items from this bulk upload
            $pendingItems = $temporaryUpload->associates()->where('status', 'pending')->get();

            if ($pendingItems->isEmpty()) {
                return back()->with('error', 'No pending items found in this bulk upload.');
            }

            // Create ItemBulk record for the entire upload
            $itemBulk = ItemBulk::create([
                'sender_name' => $temporaryUpload->user->name,
                'service_type' => $pendingItems->first()->service_type ?? 'register_post',
                'location_id' => $temporaryUpload->location_id,
                'created_by' => $currentUser->id,
                'category' => 'temporary_list',
                'item_quantity' => $pendingItems->count(),
            ]);

            $acceptedCount = 0;
            $barcodes = [];

            // Check that all items have barcodes before accepting entire upload
            $itemsWithoutBarcode = [];
            foreach ($pendingItems as $item) {
                if (!$item->barcode) {
                    $itemsWithoutBarcode[] = "Item ID: {$item->id} (Receiver: {$item->receiver_name})";
                }
            }

            if (!empty($itemsWithoutBarcode)) {
                DB::rollback();
                $missingList = implode(', ', $itemsWithoutBarcode);
                return back()->with('error', "Cannot accept entire bulk upload. The following items are missing barcodes: {$missingList}. Please add barcodes to all items first.");
            }

            // Accept all pending items in the bulk upload
            foreach ($pendingItems as $item) {
                // Use existing barcode (PM must have added barcodes for all items)
                $barcode = $item->barcode;  // Guaranteed to exist at this point
                $barcodes[] = $barcode;

                // Create Item record from temporary data
                $newItem = Item::create([
                    'item_bulk_id' => $itemBulk->id,
                    'barcode' => $barcode,
                    'receiver_name' => $item->receiver_name,
                    'receiver_address' => $item->receiver_address,
                    'status' => 'accept',
                    'weight' => $item->weight,
                    'amount' => $item->amount ?? 0,
                    'created_by' => $temporaryUpload->user_id, // Original customer
                    'updated_by' => $currentUser->id, // PM who accepted
                ]);

                // Log SMS notification for acceptance
                SmsSent::create([
                    'item_id' => $newItem->id,
                    'sender_mobile' => $temporaryUpload->user->mobile ?? '',
                    'receiver_mobile' => $item->contact_number ?? '',
                    'status' => 'accept',
                ]);

                // Update temporary record status
                $item->status = 'accept';
                $item->save();

                $acceptedCount++;
            }

            DB::commit();

            return back()->with('success', "Entire bulk upload accepted successfully! {$acceptedCount} items moved to final system. ItemBulk ID: {$itemBulk->id}, Category: temporary_list");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error accepting bulk upload: ' . $e->getMessage());
            return back()->with('error', 'Error accepting bulk upload: ' . $e->getMessage());
        }
    }

    /**
     * Generate a random 6-digit passcode for receipts
     */
    private function generatePasscode()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Item Management Interface
     */
    public function management()
    {
        // Simple interface focused only on barcode scanning
        return view('pm.item-management.index');
    }

    /**
     * Search item by barcode
     */
    public function searchByBarcode(Request $request)
    {
        try {
            $request->validate([
                'barcode' => 'required|string'
            ]);

            $currentUser = Auth::guard('pm')->user();
            $barcode = trim($request->barcode);

            // Simple logging
            Log::info('PM Item Search', [
                'barcode' => $barcode,
                'user' => $currentUser ? $currentUser->name : 'No user'
            ]);

            if (!$currentUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication error'
                ]);
            }

            // Search in main items table (removed location restriction)
            $item = Item::with(['itemBulk', 'creator', 'updater'])
                ->where('barcode', $barcode)
                ->first();

            if ($item) {
                Log::info('Item found', ['item_id' => $item->id]);

                return response()->json([
                    'success' => true,
                    'type' => 'processed',
                    'item' => $item,
                    'message' => 'Item found successfully'
                ]);
            }

            // Search in temporary upload associates (removed location restriction)
            $tempItem = TemporaryUploadAssociate::with(['temporaryUpload.user'])
                ->where('barcode', $barcode)
                ->first();

            if ($tempItem) {
                Log::info('Temporary item found', ['temp_id' => $tempItem->id]);

                return response()->json([
                    'success' => true,
                    'type' => 'temporary',
                    'item' => $tempItem,
                    'message' => 'Item found in temporary uploads (not yet processed)'
                ]);
            }

            Log::info('No item found');

            return response()->json([
                'success' => false,
                'message' => 'Item not found with barcode: ' . $barcode
            ]);

        } catch (Exception $e) {
            Log::error('Search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching'
            ]);
        }
    }

    /**
     * Edit item form
     */
    public function editItem($id)
    {
        $currentUser = Auth::guard('pm')->user();

        // Remove location restriction - allow any PM to edit any item
        $item = Item::with(['itemBulk', 'creator', 'updater'])
            ->findOrFail($id);

        return view('pm.item-management.edit', compact('item'));
    }

    /**
     * Update item
     */
    public function updateItem(Request $request, $id)
    {
        $request->validate([
            'barcode' => 'required|string|unique:items,barcode,' . $id,
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0'
        ]);

        $currentUser = Auth::guard('pm')->user();

        // Remove location restriction - allow any PM to update any item
        $item = Item::findOrFail($id);

        $item->update([
            'barcode' => $request->barcode,
            'receiver_name' => $request->receiver_name,
            'receiver_address' => $request->receiver_address,
            'weight' => $request->weight,
            'amount' => $request->amount,
            'updated_by' => $currentUser->id
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'item' => $item->load(['itemBulk', 'creator', 'updater'])
            ]);
        }

        return redirect()->route('pm.item-management.index')
            ->with('success', 'Item updated successfully');
    }

    /**
     * Delete item - Updates item status to 'delete' and marks related records as deleted
     * Does NOT permanently delete from database or change quantities
     */
    public function deleteItem($id)
    {
        $currentUser = Auth::guard('pm')->user();

        // Remove location restriction - allow any PM to delete any item
        $item = Item::with(['itemBulk.receipts', 'payments', 'smsSents'])
            ->findOrFail($id);

        // Check if item can be deleted (not dispatched or delivered)
        if (in_array($item->status, ['dispatched', 'delivered'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete item that has been dispatched or delivered'
            ]);
        }

        DB::beginTransaction();
        try {
            $barcode = $item->barcode;
            $itemBulk = $item->itemBulk;
            $itemAmount = $item->amount;

            // Find the related receipt
            $receipt = $itemBulk->receipts()->where('dlt_status', false)->first();

            if ($receipt) {
                // For bulk/temporary lists: Update receipt quantity and amount when deleting items
                if ($receipt->item_quantity > 1) {
                    // Decrease quantity by 1 and subtract item amount
                    $newQuantity = $receipt->item_quantity - 1;
                    $newAmount = $receipt->amount - $itemAmount;

                    $receipt->update([
                        'item_quantity' => $newQuantity,
                        'amount' => $newAmount,
                        'updated_by' => $currentUser->id,
                    ]);

                    Log::info('Receipt quantity and amount updated for item deletion', [
                        'receipt_id' => $receipt->id,
                        'old_quantity' => $receipt->item_quantity + 1,
                        'new_quantity' => $newQuantity,
                        'old_amount' => $receipt->amount + $itemAmount,
                        'new_amount' => $newAmount,
                        'deleted_item_amount' => $itemAmount,
                    ]);
                } else {
                    // If this is the last item, mark receipt as deleted but preserve original values
                    $receipt->update([
                        'dlt_status' => true,
                        'updated_by' => $currentUser->id,
                    ]);

                    Log::info('Receipt marked as deleted - last item in bulk', [
                        'receipt_id' => $receipt->id,
                        'final_quantity' => $receipt->item_quantity,
                        'final_amount' => $receipt->amount,
                    ]);
                }
            }

            // Handle Payment records for COD items
            if ($item->amount > 0) {
                $payments = $item->payments;
                foreach ($payments as $payment) {
                    // Soft delete payment by updating status to 'delete' (enum value)
                    $payment->update([
                        'status' => 'delete',
                        'updated_at' => now(),
                    ]);
                }

                Log::info('Payment records marked as deleted', [
                    'item_id' => $item->id,
                    'payments_deleted' => $payments->count(),
                ]);
            }

            // Handle SMS records - update status to 'delete'
            $smsRecords = $item->smsSents;
            if ($smsRecords->count() > 0) {
                foreach ($smsRecords as $sms) {
                    $sms->update([
                        'status' => 'delete',
                        'updated_at' => now(),
                    ]);
                }

                Log::info('SMS records marked as deleted', [
                    'item_id' => $item->id,
                    'sms_records_deleted' => $smsRecords->count(),
                ]);
            }

            // Update ItemBulk quantity to reflect active items
            if ($itemBulk->item_quantity > 1) {
                $itemBulk->update([
                    'item_quantity' => $itemBulk->item_quantity - 1,
                ]);

                Log::info('ItemBulk quantity updated for item deletion', [
                    'item_bulk_id' => $itemBulk->id,
                    'old_quantity' => $itemBulk->item_quantity + 1,
                    'new_quantity' => $itemBulk->item_quantity,
                ]);
            } else {
                // If this was the last item, set quantity to 0
                $itemBulk->update([
                    'item_quantity' => 0,
                ]);

                Log::info('ItemBulk quantity set to 0 - last item deleted', [
                    'item_bulk_id' => $itemBulk->id,
                ]);
            }

            // Update item status to 'delete' instead of permanently deleting
            // Do NOT change ItemBulk quantity - keep original count for statistics
            $item->update([
                'status' => 'delete',
                'updated_by' => $currentUser->id,
                'updated_at' => now(),
            ]);

            DB::commit();

            Log::info('Item status updated to delete with proper cleanup', [
                'item_id' => $id,
                'barcode' => $barcode,
                'user_id' => $currentUser->id,
                'receipt_updated' => $receipt ? true : false,
                'item_status' => 'delete',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item with barcode ' . $barcode . ' marked as deleted. Receipt quantity and amount updated accordingly.'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting item', [
                'item_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $currentUser->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting item: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get items list for AJAX
     */
    public function itemsList(Request $request)
    {
        $currentUser = Auth::guard('pm')->user();
        $search = $request->get('search');
        $status = $request->get('status');
        $perPage = $request->get('per_page', 15);

        $query = Item::with(['itemBulk', 'creator', 'updater'])
            ->whereHas('itemBulk', function ($query) use ($currentUser) {
                $query->where('location_id', $currentUser->location_id);
            });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('barcode', 'like', '%' . $search . '%')
                  ->orWhere('receiver_name', 'like', '%' . $search . '%')
                  ->orWhere('receiver_address', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
            $query->where('status', $status);
        } else {
            // If no specific status requested, exclude deleted items from normal view
            $query->where('status', '!=', 'delete');
        }

        $items = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'items' => $items
        ]);
    }
}
