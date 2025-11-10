<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\Receipt;
use App\Models\SlpPricing;
use App\Models\PostPricing;
use App\Models\Location;
use App\Models\SmsSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PMSingleItemController extends Controller
{
    public function index()
    {
        $user = Auth::guard('pm')->user();

        return view('pm.single-item.index', [
            'user' => $user,
            'location' => $user->location
        ]);
    }

    public function showSLPForm()
    {
        return view('pm.single-item.slp-form');
    }

    public function showCODForm()
    {
        return view('pm.single-item.cod-form');
    }

    public function showRegisterForm()
    {
        return view('pm.single-item.register-form');
    }

    public function storeSLP(Request $request)
    {
        $request->validate([
            'sender_name' => 'required|string|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'receiver_mobile' => 'required|string|max:15',
            'weight' => 'required|numeric|min:0.01',
            'barcode' => 'required|string|unique:items,barcode',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::guard('pm')->user();

            // Calculate postage using SLP pricing
            $postage = SlpPricing::calculatePrice($request->weight);

            // Create ItemBulk entry
            $itemBulk = ItemBulk::create([
                'sender_name' => $request->sender_name,
                'service_type' => 'slp_courier',
                'location_id' => $user->location_id,
                'created_by' => $user->id,
                'category' => 'single_item',
                'item_quantity' => 1
            ]);

            // Create Item entry - SLP has no COD charges, only postage
            $item = Item::create([
                'item_bulk_id' => $itemBulk->id,
                'barcode' => $request->barcode,
                'receiver_name' => $request->receiver_name,
                'receiver_address' => $request->receiver_address,
                'weight' => $request->weight,
                'amount' => 0, // No COD charges for SLP
                'status' => 'accept',
                'created_by' => $user->id
            ]);

            // Create SMS entry for receiver mobile
            SmsSent::create([
                'item_id' => $item->id,
                'sender_mobile' => $user->mobile ?? '',
                'receiver_mobile' => $request->receiver_mobile,
                'status' => 'accept'
            ]);

            // Create Receipt with proper postage calculation
            $receipt = Receipt::create([
                'item_quantity' => 1,
                'item_bulk_id' => $itemBulk->id,
                'amount' => 0, // SLP has no COD charges, only postage
                'postage' => $postage, // Store calculated postage
                'total_amount' => $postage, // For SLP, total = postage only
                'payment_type' => 'cash',
                'created_by' => $user->id,
                'location_id' => $user->location_id,
                'passcode' => $this->generatePasscode()
            ]);

            DB::commit();

            return redirect()
                ->route('pm.single-item.receipt', $receipt->id)
                ->with('success', 'SLP item created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create item: ' . $e->getMessage()]);
        }
    }

    public function storeCOD(Request $request)
    {
        $request->validate([
            'sender_name' => 'required|string|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'receiver_mobile' => 'required|string|max:15',
            'weight' => 'required|numeric|min:0.01',
            'amount' => 'required|numeric|min:0.01',
            'barcode' => 'required|string|unique:items,barcode',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::guard('pm')->user();

            // Calculate postage using Post pricing for COD
            $postage = PostPricing::calculatePrice($request->weight, 'cod');
            $totalAmount = $request->amount + $postage;

            // Create ItemBulk entry with notes containing COD details
            $itemBulk = ItemBulk::create([
                'sender_name' => $request->sender_name,
                'service_type' => 'cod',
                'location_id' => $user->location_id,
                'created_by' => $user->id,
                'category' => 'single_item',
                'item_quantity' => 1
            ]);

            // Create Item entry - store only COD amount, not postage
            $item = Item::create([
                'item_bulk_id' => $itemBulk->id,
                'barcode' => $request->barcode,
                'receiver_name' => $request->receiver_name,
                'receiver_address' => $request->receiver_address,
                'weight' => $request->weight,
                'amount' => $request->amount, // Only COD charges
                'status' => 'accept',
                'created_by' => $user->id
            ]);

            // Create SMS entry for receiver mobile
            SmsSent::create([
                'item_id' => $item->id,
                'sender_mobile' => $user->mobile ?? '',
                'receiver_mobile' => $request->receiver_mobile,
                'status' => 'accept'
            ]);

            // Create Receipt with proper postage and total calculation
            $receipt = Receipt::create([
                'item_quantity' => 1,
                'item_bulk_id' => $itemBulk->id,
                'amount' => $request->amount, // COD amount
                'postage' => $postage, // Store calculated postage
                'total_amount' => $totalAmount, // COD amount + postage
                'payment_type' => 'cash',
                'created_by' => $user->id,
                'location_id' => $user->location_id,
                'passcode' => $this->generatePasscode()
            ]);

            DB::commit();

            return redirect()
                ->route('pm.single-item.receipt', $receipt->id)
                ->with('success', 'COD item created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create item: ' . $e->getMessage()]);
        }
    }

    public function storeRegister(Request $request)
    {
        $request->validate([
            'sender_name' => 'required|string|max:255',
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'receiver_mobile' => 'required|string|max:15',
            'weight' => 'required|numeric|min:0.01',
            'barcode' => 'required|string|unique:items,barcode',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::guard('pm')->user();

            // Calculate postage using Post pricing for register
            $postage = PostPricing::calculatePrice($request->weight, 'register');

            // Create ItemBulk entry
            $itemBulk = ItemBulk::create([
                'sender_name' => $request->sender_name,
                'service_type' => 'register_post',
                'location_id' => $user->location_id,
                'created_by' => $user->id,
                'category' => 'single_item',
                'item_quantity' => 1
            ]);

            // Create Item entry - Register has no COD charges, only postage
            $item = Item::create([
                'item_bulk_id' => $itemBulk->id,
                'barcode' => $request->barcode,
                'receiver_name' => $request->receiver_name,
                'receiver_address' => $request->receiver_address,
                'weight' => $request->weight,
                'amount' => 0, // No COD charges for Register
                'status' => 'accept',
                'created_by' => $user->id
            ]);

            // Create SMS entry for receiver mobile
            SmsSent::create([
                'item_id' => $item->id,
                'sender_mobile' => $user->mobile ?? '',
                'receiver_mobile' => $request->receiver_mobile,
                'status' => 'accept'
            ]);

            // Create Receipt with proper postage calculation
            $receipt = Receipt::create([
                'item_quantity' => 1,
                'item_bulk_id' => $itemBulk->id,
                'amount' => 0, // Register Post has no COD charges, only postage
                'postage' => $postage, // Store calculated postage
                'total_amount' => $postage, // For Register Post, total = postage only
                'payment_type' => 'cash',
                'created_by' => $user->id,
                'location_id' => $user->location_id,
                'passcode' => $this->generatePasscode()
            ]);

            DB::commit();

            return redirect()
                ->route('pm.single-item.receipt', $receipt->id)
                ->with('success', 'Register Post item created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create item: ' . $e->getMessage()]);
        }
    }

    public function calculatePostage(Request $request)
    {
        $request->validate([
            'weight' => 'required|numeric|min:0.01',
            'service_type' => 'required|in:slp_courier,cod,register_post'
        ]);

        $weight = $request->weight;
        $serviceType = $request->service_type;

        try {
            $postage = 0;

            switch ($serviceType) {
                case 'slp_courier':
                    $postage = SlpPricing::calculatePrice($weight);
                    break;
                case 'cod':
                    $postage = PostPricing::calculatePrice($weight, 'cod');
                    break;
                case 'register_post':
                    $postage = PostPricing::calculatePrice($weight, 'register');
                    break;
            }

            return response()->json([
                'success' => true,
                'postage' => number_format($postage, 2),
                'weight' => $weight,
                'service_type' => $serviceType
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate postage: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showReceipt($receiptId)
    {
        $receipt = Receipt::with([
            'itemBulk.creator.location',
            'itemBulk.items.smsSents',
            'location'
        ])->findOrFail($receiptId);

        // Ensure PM can only view receipts from their location
        $user = Auth::guard('pm')->user();
        if ($receipt->location_id !== $user->location_id) {
            abort(403, 'Unauthorized access to receipt');
        }

        return view('pm.single-item.receipt', compact('receipt'));
    }

    public function printReceipt($receiptId)
    {
        $receipt = Receipt::with([
            'itemBulk.creator.location',
            'itemBulk.items.smsSents',
            'location'
        ])->findOrFail($receiptId);

        // Ensure PM can only print receipts from their location
        $user = Auth::guard('pm')->user();
        if ($receipt->location_id !== $user->location_id) {
            abort(403, 'Unauthorized access to receipt');
        }

        return view('pm.single-item.print-receipt', compact('receipt'));
    }

    private function generatePasscode()
    {
        return strtoupper(Str::random(8));
    }
}
