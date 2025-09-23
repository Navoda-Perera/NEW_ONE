<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceType;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\TemporaryUpload;
use App\Models\SlpPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();
        return view('customer.dashboard', compact('user'));
    }

    public function profile()
    {
        /** @var User $user */
        $user = Auth::user();
        return view('customer.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users,nic,' . Auth::id(),
            'email' => 'nullable|string|email|max:255',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->name = $request->name;
        $user->nic = $request->nic;
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Password updated successfully!');
    }

    // Postal Services Methods
    public function services()
    {
        /** @var User $user */
        $user = Auth::user();
        $totalItems = Item::where('created_by', $user->id)->count();
        $pendingItems = Item::where('created_by', $user->id)->where('status', 'accept')->count();
        $deliveredItems = Item::where('created_by', $user->id)->where('status', 'delivered')->count();

        return view('customer.services.index', compact('user', 'totalItems', 'pendingItems', 'deliveredItems'));
    }

    public function addSingleItem()
    {
        /** @var User $user */
        $user = Auth::user();
        $serviceTypes = ServiceType::active()->get();
        $slpPricing = SlpPricing::getPricingTiers();

        return view('customer.services.add-single-item', compact('user', 'serviceTypes', 'slpPricing'));
    }

    public function storeSingleItem(Request $request)
    {
        $rules = [
            'service_type_id' => 'required|exists:service_types,id',
            'receiver_name' => 'required|string|max:255',
            'address' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ];

        // Add weight validation for SLP Courier
        $serviceType = ServiceType::find($request->service_type_id);
        if ($serviceType && $serviceType->has_weight_pricing) {
            $rules['weight'] = 'required|numeric|min:1|max:40000';
        }

        $request->validate($rules);

        /** @var User $user */
        $user = Auth::user();

        // Calculate postage
        $postage = 0;
        if ($serviceType->has_weight_pricing) {
            $postage = SlpPricing::calculatePrice($request->weight) ?? 0;
        } else {
            $postage = $serviceType->base_price ?? 0;
        }

        // Calculate commission (5% of amount)
        $commission = $request->amount * 0.05;

        $item = Item::create([
            'receiver_name' => $request->receiver_name,
            'address' => $request->address,
            'status' => 'accept',
            'weight' => $request->weight,
            'amount' => $request->amount,
            'service_type_id' => $request->service_type_id,
            'created_by' => $user->id,
            'postage' => $postage,
            'commission' => $commission,
            'notes' => $request->notes,
        ]);

        // Create item bulk record for tracking
        ItemBulk::create([
            'sender_name' => $user->name,
            'service_type_id' => $request->service_type_id,
            'location_id' => 1, // Default location, can be modified
            'created_by' => $user->id,
            'category' => 'single_item',
            'total_items' => 1,
            'total_amount' => $request->amount,
            'total_postage' => $postage,
            'total_commission' => $commission,
            'status' => 'pending',
        ]);

        return redirect()->route('customer.services.items')->with('success', 'Item added successfully! Tracking Number: ' . $item->tracking_number);
    }

    public function bulkUpload()
    {
        /** @var User $user */
        $user = Auth::user();
        $serviceTypes = ServiceType::active()->get();

        return view('customer.services.bulk-upload', compact('user', 'serviceTypes'));
    }

    public function storeBulkUpload(Request $request)
    {
        $request->validate([
            'service_type_id' => 'required|exists:service_types,id',
            'bulk_file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Store the uploaded file
        $file = $request->file('bulk_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('bulk_uploads', $filename, 'public');

        // Create temporary upload record
        $temporaryUpload = TemporaryUpload::create([
            'location_id' => 1, // Default location
            'user_id' => $user->id,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'total_items' => 0, // Will be updated after processing
            'status' => 'pending',
        ]);

        return redirect()->route('customer.services.bulk-status', $temporaryUpload->id)
            ->with('success', 'File uploaded successfully! Processing will begin shortly.');
    }

    public function items()
    {
        /** @var User $user */
        $user = Auth::user();
        $items = Item::with('serviceType')
            ->where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('customer.services.items', compact('user', 'items'));
    }

    public function bulkStatus($id)
    {
        /** @var User $user */
        $user = Auth::user();
        $temporaryUpload = TemporaryUpload::with('associates')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return view('customer.services.bulk-status', compact('user', 'temporaryUpload'));
    }

    // AJAX method to get SLP pricing for weight
    public function getSlpPrice(Request $request)
    {
        $weight = $request->input('weight');
        $price = SlpPricing::calculatePrice($weight);

        return response()->json([
            'price' => $price,
            'formatted_price' => $price ? 'LKR ' . number_format($price, 2) : 'No pricing available'
        ]);
    }
}
