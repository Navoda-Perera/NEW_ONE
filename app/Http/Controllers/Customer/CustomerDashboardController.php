<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\SlpPricing;
use App\Models\PostPricing;
use App\Models\Location;
use App\Models\ItemAdditionalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $locations = Location::active()->get();

        $serviceTypes = [
            'register_post' => [
                'label' => 'Register Post',
                'has_weight' => true,
                'base_price' => 50
            ],
            'slp_courier' => [
                'label' => 'SLP Courier',
                'has_weight' => true,
                'base_price' => 100
            ],
            'cod' => [
                'label' => 'COD',
                'has_weight' => true,
                'base_price' => 75
            ],
            'remittance' => [
                'label' => 'Remittance',
                'has_weight' => false,
                'base_price' => 25
            ]
        ];

        return view('customer.services.add-single-item', compact('user', 'locations', 'serviceTypes'));
    }

    public function storeSingleItem(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Validation rules for items
        $rules = [
            'service_type' => 'required|in:register_post,slp_courier,cod,remittance',
            'receiver_name' => 'required|string|max:255',
            'address' => 'required|string', // Form field name is 'address' but maps to receiver_address in DB
            'amount' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:1|max:40000',
            'barcode' => 'nullable|string|max:255', // Optional barcode from customer
        ];

        $request->validate($rules);

        // Create temporary upload record
        $temporaryUpload = TemporaryUpload::create([
            'service_type' => $request->service_type,
            'location_id' => $user->location_id ?? 1,
            'user_id' => $user->id,
        ]);

        // Calculate postage (you can implement proper calculation logic here)
        $postage = $this->calculatePostage($request->service_type, $request->weight, $request->amount);

        // Create temporary upload associate record with item details
        $temporaryAssociate = TemporaryUploadAssociate::create([
            'temporary_id' => $temporaryUpload->id,
            'sender_name' => $user->name,
            'receiver_name' => $request->receiver_name,
            'receiver_address' => $request->address,
            'weight' => $request->weight,
            'amount' => $request->amount,
            'postage' => $postage,
            'barcode' => $request->barcode, // Optional barcode from customer
            'status' => 'pending', // Status is pending until PM accepts
        ]);

        // Create item bulk record for tracking service type
        ItemBulk::create([
            'sender_name' => $user->name,
            'service_type' => $request->service_type,
            'location_id' => $user->location_id ?? 1,
            'created_by' => $user->id,
            'category' => 'single_item',
            'item_quantity' => 1,
        ]);

        $message = 'Item submitted successfully! Status: Pending PM approval.';
        if ($request->barcode) {
            $message .= ' Your barcode: ' . $request->barcode;
        } else {
            $message .= ' PM will assign barcode after acceptance.';
        }

        return redirect()->route('customer.services.items')->with('success', $message);
    }

    private function storeItemAdditionalDetail(Request $request, User $user)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'address' => 'required|string', // Form field name is 'address' but maps to receiver_address in DB
            'amount' => 'required|numeric|min:0',
            'commission' => 'required|numeric|min:0',
            'service_type' => 'required|in:remittance,insured',
        ]);

        // Determine the type based on service_type
        $type = $request->service_type === 'remittance' ? ItemAdditionalDetail::TYPE_REMITTANCE : ItemAdditionalDetail::TYPE_INSURED;

        $itemDetail = ItemAdditionalDetail::create([
            'type' => $type,
            'amount' => $request->amount,
            'commission' => $request->commission,
            'created_by' => $user->id,
            'location_id' => $user->location_id ?? 1,
            'receiver_name' => $request->receiver_name,
            'receiver_address' => $request->address,
            'status' => 'pending',
        ]);

        $typeLabel = $type === ItemAdditionalDetail::TYPE_REMITTANCE ? 'Remittance' : 'Insured';
        return redirect()->route('customer.services.items')->with('success', $typeLabel . ' record created successfully! Reference: IAD-' . $itemDetail->id);
    }

    public function bulkUpload()
    {
        /** @var User $user */
        $user = Auth::user();

        // For bulk upload, we just need simple key-value pairs
        $serviceTypes = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD',
            'remittance' => 'Remittance'
        ];

        return view('customer.services.bulk-upload', compact('user', 'serviceTypes'));
    }

    public function storeBulkUpload(Request $request)
    {
        $request->validate([
            'service_type' => 'required|in:register_post,slp_courier,cod,remittance',
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
            'service_type' => $request->service_type,
            'location_id' => $user->location_id ?? 1,
            'user_id' => $user->id,
        ]);

        return redirect()->route('customer.services.bulk-status', $temporaryUpload->id)
            ->with('success', 'File uploaded successfully! Processing will begin shortly.');
    }

    public function items(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Get items from TemporaryUploadAssociate with TemporaryUpload relationship
        $query = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('temporaryUpload');

        // Apply status filter if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get service types mapping for items
        $serviceTypeLabels = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD',
            'remittance' => 'Remittance'
        ];

        return view('customer.services.items', compact('user', 'items', 'serviceTypeLabels'));
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
        Log::info('Pricing request received', ['weight' => $weight]);

        $price = SlpPricing::calculatePrice($weight);
        Log::info('Calculated price', ['weight' => $weight, 'price' => $price]);

        return response()->json([
            'price' => $price,
            'formatted_price' => $price ? 'LKR ' . number_format($price, 2) : 'No pricing available'
        ]);
    }

    // AJAX method to get basic pricing for weight
    public function getPostalPrice(Request $request)
    {
        $weight = $request->input('weight');

        Log::info('Postal pricing request received', ['weight' => $weight]);

        // Basic pricing calculation (could be customized as needed)
        $price = $weight * 10; // 10 LKR per gram as basic pricing

        Log::info('Calculated postal price', ['weight' => $weight, 'price' => $price]);

        return response()->json([
            'price' => $price,
            'formatted_price' => 'LKR ' . number_format($price, 2)
        ]);
    }

    private function calculatePostage($serviceType, $weight, $amount)
    {
        switch ($serviceType) {
            case 'register_post':
                $price = PostPricing::calculatePrice($weight, PostPricing::TYPE_REGISTER);
                return $price ?? ($weight * 0.1); // Fallback to basic calculation

            case 'slp_courier':
                $price = SlpPricing::calculatePrice($weight);
                return $price ?? ($weight * 0.15); // Fallback to basic calculation

            case 'cod':
                // COD typically uses register post pricing + COD fee
                $basePrice = PostPricing::calculatePrice($weight, PostPricing::TYPE_REGISTER);
                $codFee = $amount * 0.02; // 2% of amount for COD
                return ($basePrice ?? ($weight * 0.12)) + $codFee;

            case 'remittance':
                return $amount * 0.03; // 3% of amount for remittance

            default:
                return $weight * 0.1; // Default fallback
        }
    }
}
