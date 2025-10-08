<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class PMDashboardController extends Controller
{
    public function index()
    {
        $customerUsers = User::where('role', 'customer')->count();
        $activeCustomers = User::where('role', 'customer')
                               ->where('is_active', true)
                               ->count();
        $externalCustomers = User::where('user_type', 'external')
                                ->where('role', 'customer')
                                ->count();

        $currentUser = Auth::user();
        $pendingItemsCount = TemporaryUploadAssociate::where('status', 'pending')
                                                    ->whereHas('temporaryUpload', function($query) use ($currentUser) {
                                                        $query->where('location_id', $currentUser->location_id);
                                                    })
                                                    ->count();

        // Get service type breakdowns from TemporaryUploadAssociate table for pending customer uploads
        $serviceTypeBreakdown = TemporaryUploadAssociate::selectRaw('service_type, COUNT(*) as count')
                                        ->where('status', 'pending')
                                        ->whereHas('temporaryUpload', function($query) use ($currentUser) {
                                            $query->where('location_id', $currentUser->location_id);
                                        })
                                        ->groupBy('service_type')
                                        ->pluck('count', 'service_type')
                                        ->toArray();

        // Ensure all service types are represented with proper labels
        $serviceTypes = [
            'register_post' => [
                'count' => $serviceTypeBreakdown['register_post'] ?? 0,
                'label' => 'Register Post',
                'icon' => 'bi-envelope',
                'color' => 'primary'
            ],
            'slp_courier' => [
                'count' => $serviceTypeBreakdown['slp_courier'] ?? 0,
                'label' => 'SLP Courier',
                'icon' => 'bi-truck',
                'color' => 'success'
            ],
            'cod' => [
                'count' => $serviceTypeBreakdown['cod'] ?? 0,
                'label' => 'COD',
                'icon' => 'bi-cash-coin',
                'color' => 'warning'
            ],
            'remittance' => [
                'count' => $serviceTypeBreakdown['remittance'] ?? 0,
                'label' => 'Remittance',
                'icon' => 'bi-currency-exchange',
                'color' => 'info'
            ]
        ];

        // Get current authenticated user with location
        $currentUser = Auth::user();
        if ($currentUser) {
            $currentUser = User::with('location')->find($currentUser->id);
        }

        return view('pm.dashboard', compact('customerUsers', 'activeCustomers', 'externalCustomers', 'currentUser', 'pendingItemsCount', 'serviceTypes'));
    }

    public function customers()
    {
        // Get current authenticated user with location using eager loading
        $currentUser = User::with('location')->find(Auth::id());

        // Get customers for this PM's location
        $customers = User::where('role', 'customer');

        // Filter by location if PM has a location assigned
        if ($currentUser && $currentUser->location_id) {
            $customers = $customers->where('location_id', $currentUser->location_id);
        }

        $customers = $customers->orderBy('created_at', 'desc')->paginate(10);

        return view('pm.customers.index', compact('customers', 'currentUser'));
    }    // Create Customer methods
    public function createCustomer()
    {
        // Get current user's location information
        $currentUser = User::with('location')->find(Auth::id());

        return view('pm.customers.create', compact('currentUser'));
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users',
            'email' => 'nullable|string|email|max:255',
            'mobile' => 'required|string|regex:/^[0-9]{10}$/',
            'company_name' => 'required|string|max:255',
            'company_br' => 'required|string|max:50',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'nic' => $request->nic,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'company_name' => $request->company_name,
            'company_br' => $request->company_br,
            'password' => Hash::make($request->password),
            'user_type' => 'external',
            'role' => 'customer',
            'is_active' => true,
        ]);

        return redirect()->route('pm.customers.index')->with('success', 'Customer created successfully!');
    }

    // Create Postmen methods
    public function postmen()
    {
        $postmen = User::where('role', 'postman')
                      ->orderBy('created_at', 'desc')
                      ->paginate(10);

        // Get current user's location information
        $currentUser = User::with('location')->find(Auth::id());

        return view('pm.postmen.index', compact('postmen', 'currentUser'));
    }

    public function createPostman()
    {
        // Get current user's location information
        $currentUser = User::with('location')->find(Auth::id());

        return view('pm.postmen.create', compact('currentUser'));
    }

    public function storePostman(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users',
            'email' => 'nullable|string|email|max:255',
            'mobile' => 'required|string|regex:/^[0-9]{10}$/',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name' => $request->name,
            'nic' => $request->nic,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'user_type' => 'internal',
            'role' => 'postman',
            'is_active' => true,
        ]);

        return redirect()->route('pm.postmen.index')->with('success', 'Postman created successfully!');
    }

    // Toggle user status
    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully!");
    }

    // PM Items Management
    public function pendingItems(Request $request)
    {
        $user = Auth::user();

        // Get pending items from the PM's location
        $query = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('location_id', $user->location_id);
        })
        ->with(['temporaryUpload.user', 'temporaryUpload'])
        ->where('status', 'pending');

        $pendingItems = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('pm.items.pending', compact('pendingItems'));
    }

    public function editItem($id)
    {
        $user = Auth::user();

        $temporaryAssociate = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('location_id', $user->location_id);
        })
        ->with(['temporaryUpload.user', 'temporaryUpload'])
        ->findOrFail($id);

        if ($temporaryAssociate->status !== 'pending') {
            return back()->with('error', 'Item is not in pending status.');
        }

        return view('pm.items.edit', compact('temporaryAssociate'));
    }

    public function acceptItem(Request $request, $id)
    {
        $user = Auth::user();

        // Validate the incoming request data for PM edits
        $request->validate([
            'weight' => 'required|numeric|min:0',
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'item_value' => 'required|numeric|min:0',
            'barcode' => 'required|string|max:50|unique:items,barcode',
        ]);

        $temporaryAssociate = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('location_id', $user->location_id);
        })
        ->with('temporaryUpload')
        ->findOrFail($id);

        if ($temporaryAssociate->status !== 'pending') {
            return back()->with('error', 'Item is not in pending status.');
        }

        DB::transaction(function() use ($temporaryAssociate, $request, $user) {
            // Use the manually entered barcode from PM
            $barcode = $request->barcode;

            // Create Item record with PM's verified details
            $item = Item::create([
                'receiver_name' => $request->receiver_name,
                'receiver_address' => $request->receiver_address,
                'status' => 'accept',
                'weight' => $request->weight,
                'amount' => $request->amount,
                'item_value' => $request->item_value,
                'barcode' => $barcode,
                'created_by' => $temporaryAssociate->temporaryUpload->user_id,
                'updated_by' => $user->id,
            ]);

            // Update temporary associate with PM's verified details and status
            $temporaryAssociate->update([
                'status' => 'accept',
                'weight' => $request->weight,
                'receiver_name' => $request->receiver_name,
                'receiver_address' => $request->receiver_address,
                'amount' => $request->amount,
                'item_value' => $request->item_value,
                'barcode' => $barcode
            ]);
        });

        $message = 'Item accepted successfully! Barcode: ' . $request->barcode;

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('pm.items.pending')->with('success', $message);
    }

    public function rejectItem(Request $request, $id)
    {
        $user = Auth::user();

        $temporaryAssociate = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('location_id', $user->location_id);
        })->findOrFail($id);

        if ($temporaryAssociate->status !== 'pending') {
            $message = 'Item is not in pending status.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return back()->with('error', $message);
        }

        $temporaryAssociate->update([
            'status' => 'reject'
        ]);

        $message = 'Item rejected successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // PM Bulk Upload Methods
    public function bulkUpload()
    {
        /** @var User $user */
        $user = Auth::user();
        $locations = Location::active()->get();

        // Service types for PM uploads
        $serviceTypes = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD',
            'remittance' => 'Remittance'
        ];

        return view('pm.bulk-upload', compact('user', 'locations', 'serviceTypes'));
    }

    public function storeBulkUpload(Request $request)
    {
        $request->validate([
            'origin_post_office_id' => 'required|exists:locations,id',
            'service_type' => 'required|string|in:register_post,slp_courier,cod,remittance',
            'bulk_file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Store the uploaded file
        $file = $request->file('bulk_file');
        $filename = time() . '_PM_' . $file->getClientOriginalName();
        $file->storeAs('bulk_uploads', $filename, 'public');

        // Parse CSV and create items directly (PM uploads go straight to final tables)
        $csvPath = $file->getPathname();
        $defaultServiceType = $request->service_type;
        $itemsCreated = 0;

        DB::beginTransaction();
        try {
            if (($handle = fopen($csvPath, 'r')) !== false) {
                $header = fgetcsv($handle);

                // Clean header
                $header = array_filter(array_map('trim', $header), function($value) {
                    return $value !== '';
                });

                while (($row = fgetcsv($handle)) !== false) {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Ensure row has same number of elements as header
                    $row = array_slice($row, 0, count($header));
                    if (count($row) < count($header)) {
                        $row = array_pad($row, count($header), '');
                    }

                    $item = array_combine($header, $row);

                    // Use service type from CSV if provided, otherwise use the selected default
                    $serviceType = $item['service_type'] ?? $defaultServiceType;

                    // Validate service type
                    if (!in_array($serviceType, ['register_post', 'slp_courier', 'cod', 'remittance'])) {
                        $serviceType = $defaultServiceType;
                    }

                    // Generate barcode
                    $barcode = 'PM' . time() . str_pad($itemsCreated + 1, 4, '0', STR_PAD_LEFT);

                    // Create Item record directly
                    $newItem = Item::create([
                        'barcode' => $barcode,
                        'receiver_name' => $item['receiver_name'] ?? '',
                        'receiver_address' => $item['receiver_address'] ?? '',
                        'status' => 'accepted', // PM uploads are automatically accepted
                        'weight' => $item['weight'] ?? 0,
                        'amount' => $item['amount'] ?? 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    // Create ItemBulk record
                    ItemBulk::create([
                        'sender_name' => $item['sender_name'] ?? $user->name,
                        'service_type' => $serviceType,
                        'location_id' => $request->origin_post_office_id,
                        'created_by' => $user->id,
                        'category' => 'bulk_list', // PM uploads use 'bulk_list' category
                        'item_quantity' => 1,
                        'item_id' => $newItem->id,
                        'notes' => $item['notes'] ?? null,
                    ]);

                    $itemsCreated++;
                }
                fclose($handle);
            }

            DB::commit();

            return redirect()->route('pm.dashboard')
                ->with('success', "Bulk upload successful! Created {$itemsCreated} items with service type: " . ucfirst(str_replace('_', ' ', $defaultServiceType)));

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['bulk_file' => 'Error processing file: ' . $e->getMessage()]);
        }
    }
}
