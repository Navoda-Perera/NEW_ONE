<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\TemporaryUpload;
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

        return view('customer.services.add-single-item', compact('user', 'locations'));
    }

    public function storeSingleItem(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Validation rules for items
        $rules = [
            'receiver_name' => 'required|string|max:255',
            'address' => 'required|string', // Form field name is 'address' but maps to receiver_address in DB
            'amount' => 'required|numeric|min:0',
            'weight' => 'required|numeric|min:1|max:40000',
        ];

        $request->validate($rules);

        $item = Item::create([
            'receiver_name' => $request->receiver_name,
            'receiver_address' => $request->address,
            'status' => 'accept',
            'weight' => $request->weight,
            'amount' => $request->amount,
            'barcode' => $request->barcode, // Optional barcode from customer
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('customer.services.items')->with('success', 'Item added successfully! Barcode: ' . $item->barcode);
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

        $query = Item::where('created_by', $user->id);

        // Apply status filter if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $items = $query->orderBy('created_at', 'desc')
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
}
