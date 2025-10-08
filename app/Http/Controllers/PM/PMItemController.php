<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\TemporaryUploadAssociate;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PMItemController extends Controller
{
    public function pending()
    {
        Log::info('PMItemController@pending accessed by user', [
            'user_id' => Auth::id(),
            'user_role' => Auth::user()->role
        ]);

        $currentUser = Auth::user();

        // Get pending items for the current PM's location
        $pendingItems = TemporaryUploadAssociate::with([
                'temporaryUpload.user',
                'temporaryUpload.location'
            ])
            ->where('status', 'pending')
            ->whereHas('temporaryUpload', function ($query) use ($currentUser) {
                $query->where('location_id', $currentUser->location_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pm.items.pending', compact('pendingItems'));
    }

    public function pendingByServiceType($serviceType)
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

        // Get pending items filtered by service type from TemporaryUploadAssociate table
        $pendingItems = TemporaryUploadAssociate::with([
                'temporaryUpload.user',
                'temporaryUpload.location'
            ])
            ->where('status', 'pending')
            ->where('service_type', $serviceType)
            ->whereHas('temporaryUpload', function ($query) use ($currentUser) {
                $query->where('location_id', $currentUser->location_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

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
            // Generate barcode for the new item
            $barcode = 'ACC' . time() . str_pad($id, 4, '0', STR_PAD_LEFT);

            // Create Item record from temporary data
            $newItem = Item::create([
                'barcode' => $barcode,
                'receiver_name' => $item->receiver_name,
                'receiver_address' => $item->receiver_address,
                'status' => 'accepted',
                'weight' => $item->weight,
                'amount' => $item->amount,
                'created_by' => $item->temporaryUpload->user_id, // Original customer
                'updated_by' => $currentUser->id, // PM who accepted
            ]);

            // Create ItemBulk record
            ItemBulk::create([
                'sender_name' => $item->sender_name,
                'service_type' => $item->service_type ?? 'register_post',
                'location_id' => $item->temporaryUpload->location_id,
                'created_by' => $currentUser->id,
                'category' => 'accepted_from_temporary',
                'item_quantity' => 1,
                'item_id' => $newItem->id,
                'notes' => $item->notes,
                'temporary_upload_associate_id' => $item->id,
            ]);

            // Update temporary record status
            $item->status = 'accepted';
            $item->processed_by = $currentUser->id;
            $item->processed_at = now();
            $item->save();

            DB::commit();

            return back()->with('success', 'Item accepted successfully and moved to final system. Barcode: ' . $barcode);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error accepting item: ' . $e->getMessage());
            return back()->with('error', 'Error accepting item: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        Log::info('PMItemController@reject called', [
            'user_id' => Auth::id(),
            'item_id' => $id
        ]);

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $item = TemporaryUploadAssociate::findOrFail($id);

        // Verify this item belongs to the PM's location
        $currentUser = Auth::user();
        if ($item->temporaryUpload->location_id !== $currentUser->location_id) {
            abort(403, 'Unauthorized access to this item.');
        }

        $item->status = 'rejected';
        $item->rejection_reason = $request->rejection_reason;
        $item->processed_by = $currentUser->id;
        $item->processed_at = now();
        $item->save();

        return back()->with('success', 'Item rejected successfully.');
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

        return view('pm.items.edit', compact('item'));
    }
}
