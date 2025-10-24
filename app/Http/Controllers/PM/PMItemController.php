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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PMItemController extends Controller
{
    public function pending(Request $request)
    {
        Log::info('PMItemController@pending accessed by user', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role
        ]);

        $currentUser = Auth::user();
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

        $currentUser = Auth::user();
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
        $currentUser = Auth::user();
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

        // Create Receipt for the accepted item (only item amount, no postage)
        $receipt = Receipt::create([
            'item_quantity' => 1,
            'item_bulk_id' => $itemBulk->id,
            'amount' => $item->amount ?? 0, // Only item amount, no postage
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
            // Look for existing ItemBulk for this temporary upload
            $existingItemBulk = ItemBulk::where('sender_name', $temporaryUpload->user->name)
                ->where('location_id', $temporaryUpload->location_id)
                ->where('category', 'temporary_list')
                ->whereHas('items', function($query) use ($temporaryUpload) {
                    // Check if any items from this temporary upload already exist
                    $query->whereIn('created_by', [$temporaryUpload->user_id]);
                })
                ->first();

            if (!$existingItemBulk) {
                // Create new ItemBulk for this temporary upload
                $itemBulk = ItemBulk::create([
                    'sender_name' => $temporaryUpload->user->name,
                    'service_type' => $item->service_type ?? 'register_post',
                    'location_id' => $temporaryUpload->location_id,
                    'created_by' => $currentUser->id,
                    'category' => 'temporary_list',
                    'item_quantity' => 1, // Will be updated as more items are added
                ]);
            } else {
                $itemBulk = $existingItemBulk;
                // Update item quantity
                $itemBulk->increment('item_quantity');
            }
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
        $currentUser = Auth::user();
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
        $currentUser = Auth::user();
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
        $currentUser = Auth::user();
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
        $currentUser = Auth::user();
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

        $currentUser = Auth::user();
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

        $currentUser = Auth::user();
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
}
