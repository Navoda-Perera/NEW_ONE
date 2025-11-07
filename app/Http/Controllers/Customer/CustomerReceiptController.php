<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\ItemBulk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerReceiptController extends Controller
{
    public function index()
    {
        $user = Auth::guard('customer')->user();

        // Get receipts for items created by this customer or sent to this customer
        // Also include receipts for items originally submitted by customer through temporary uploads
        $receipts = Receipt::with([
            'itemBulk.creator',
            'itemBulk.items.smsSents',
            'location'
        ])
        ->whereHas('itemBulk', function($query) use ($user) {
            $query->where('created_by', $user->id) // Items created directly by customer
                  ->orWhereHas('items', function($itemQuery) use ($user) {
                      // Items where customer is listed as original creator
                      $itemQuery->where('created_by', $user->id)
                                ->orWhereHas('smsSents', function($smsQuery) use ($user) {
                                    // Match by receiver mobile from SMS table
                                    $smsQuery->where('receiver_mobile', $user->mobile);
                                });
                  });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        // Calculate summary data
        $allReceipts = Receipt::with(['itemBulk.items'])
        ->whereHas('itemBulk', function($query) use ($user) {
            $query->where('created_by', $user->id)
                  ->orWhereHas('items', function($itemQuery) use ($user) {
                      $itemQuery->where('created_by', $user->id)
                                ->orWhereHas('smsSents', function($smsQuery) use ($user) {
                                    $smsQuery->where('receiver_mobile', $user->mobile);
                                });
                  });
        })->get();

        $totalReceipts = $allReceipts->count();

        $totalItems = $allReceipts->sum(function($receipt) {
            return $receipt->itemBulk ? $receipt->itemBulk->items->count() : 0;
        });

        return view('customer.receipts.index', compact('receipts', 'totalReceipts', 'totalItems'));
    }    public function show($id)
    {
        $user = Auth::guard('customer')->user();

        $receipt = Receipt::with([
            'itemBulk.creator.location',
            'itemBulk.items.smsSents',
            'location'
        ])
        ->whereHas('itemBulk', function($query) use ($user) {
            $query->where('created_by', $user->id) // Items created directly by customer
                  ->orWhereHas('items', function($itemQuery) use ($user) {
                      // Items where customer is listed as original creator
                      $itemQuery->where('created_by', $user->id)
                                ->orWhereHas('smsSents', function($smsQuery) use ($user) {
                                    // Match by receiver mobile from SMS table
                                    $smsQuery->where('receiver_mobile', $user->mobile);
                                });
                  });
        })
        ->findOrFail($id);

        return view('customer.receipts.show', compact('receipt'));
    }

    public function download($id)
    {
        $user = Auth::guard('customer')->user();

        $receipt = Receipt::with([
            'itemBulk.creator.location',
            'itemBulk.items.smsSents',
            'location'
        ])
        ->whereHas('itemBulk', function($query) use ($user) {
            $query->where('created_by', $user->id) // Items created directly by customer
                  ->orWhereHas('items', function($itemQuery) use ($user) {
                      // Items where customer is listed as original creator
                      $itemQuery->where('created_by', $user->id)
                                ->orWhereHas('smsSents', function($smsQuery) use ($user) {
                                    // Match by receiver mobile from SMS table
                                    $smsQuery->where('receiver_mobile', $user->mobile);
                                });
                  });
        })
        ->findOrFail($id);

        return view('customer.receipts.download', compact('receipt'));
    }

    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $user = Auth::guard('customer')->user();
        $barcode = $request->barcode;

        $receipt = Receipt::with([
            'itemBulk.creator.location',
            'itemBulk.items.smsSents',
            'location'
        ])
        ->whereHas('itemBulk.items', function($query) use ($barcode) {
            $query->where('barcode', $barcode);
        })
        ->whereHas('itemBulk', function($query) use ($user) {
            $query->where('created_by', $user->id) // Items created directly by customer
                  ->orWhereHas('items', function($itemQuery) use ($user) {
                      // Items where customer is listed as original creator
                      $itemQuery->where('created_by', $user->id)
                                ->orWhereHas('smsSents', function($smsQuery) use ($user) {
                                    // Match by receiver mobile from SMS table
                                    $smsQuery->where('receiver_mobile', $user->mobile);
                                });
                  });
        })
        ->first();

        if ($receipt) {
            return redirect()->route('customer.receipts.show', $receipt->id)
                           ->with('success', 'Receipt found for barcode: ' . $barcode);
        }

        return back()->withErrors(['barcode' => 'No receipt found for this barcode or you do not have access to it.']);
    }
}
