<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\TemporaryUploadAssociate;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $pendingItems = TemporaryUploadAssociate::with(['temporaryUpload.user', 'temporaryUpload.location'])
            ->where('status', 'pending')
            ->whereHas('temporaryUpload', function ($query) use ($currentUser) {
                $query->where('location_id', $currentUser->location_id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('pm.items.pending', compact('pendingItems'));
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

        $item->status = 'accepted';
        $item->processed_by = $currentUser->id;
        $item->processed_at = now();
        $item->save();

        return back()->with('success', 'Item accepted successfully.');
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
