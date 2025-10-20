<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use App\Models\SmsSent;
use App\Models\TemporaryUploadAssociate;
use App\Models\Item;
use App\Models\ItemBulk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PMDashboardController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get customer statistics
        $customerUsers = User::where('role', 'customer')->count();
        $activeCustomers = User::where('role', 'customer')
                               ->where('is_active', true)
                               ->count();
        $externalCustomers = User::where('role', 'external_customer')->count();

        // Get pending items count for PM's location
        $pendingItemsCount = TemporaryUploadAssociate::where('status', 'pending')
            ->whereHas('temporaryUpload', function ($query) use ($currentUser) {
                $query->where('location_id', $currentUser->location_id);
            })
            ->count();

        // Get service type statistics for PM's location - removed all service type cards
        $serviceTypes = [];

        $locations = Location::where('is_active', true)->count();

        // Load the user with location relationship for the view
        $currentUser = User::with('location')->find($currentUser->id);

        return view('pm.dashboard', compact(
            'customerUsers',
            'activeCustomers',
            'externalCustomers',
            'currentUser',
            'pendingItemsCount',
            'serviceTypes',
            'locations'
        ));
    }

    public function customers(Request $request)
    {
        $currentUser = Auth::user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get customers assigned to this PM's location only
        $customersQuery = User::with(['location'])
            ->where('role', 'customer')
            ->where('location_id', $currentUser->location_id);

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $customersQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $customers = $customersQuery->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pm.customers', compact('customers'));
    }

    public function createCustomer()
    {
        $currentUser = Auth::user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get only the PM's assigned location
        $locations = Location::where('id', $currentUser->location_id)->get();

        return view('pm.create-customer', compact('locations'));
    }

    public function storeCustomer(Request $request)
    {
        $currentUser = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nic' => 'required|string|max:12|unique:users',
            'mobile' => 'required|string|max:15',
            'address' => 'required|string|max:500',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create customer with PM's location only
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nic' => $request->nic,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'role' => 'customer',
            'location_id' => $currentUser->location_id, // Assign to PM's location
            'is_active' => true,
        ]);

        return redirect()->route('pm.customers.index')->with('success', 'Customer created successfully!');
    }

    public function customerUploads(Request $request)
    {
        $currentUser = Auth::user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get customer uploads with pending items grouped by customer and upload
        $uploadsQuery = \App\Models\TemporaryUpload::with(['user', 'location'])
            ->where('location_id', $currentUser->location_id)
            ->whereHas('associates', function($q) {
                $q->where('status', 'pending');
            })
            ->withCount([
                'associates as total_items',
                'associates as pending_items' => function($query) {
                    $query->where('status', 'pending');
                }
            ]);

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $uploadsQuery->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%")
                               ->orWhere('nic', 'like', "%{$search}%");
                  });
            });
        }

        // Apply service type filter
        if ($request->has('service_type') && $request->service_type) {
            $uploadsQuery->whereHas('associates', function($q) use ($request) {
                $q->where('service_type', $request->service_type);
            });
        }

        $uploads = $uploadsQuery->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get service types mapping
        $serviceTypeLabels = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD'
        ];

        return view('pm.customer-uploads', compact('uploads', 'serviceTypeLabels'));
    }

    public function postmen(Request $request)
    {
        $currentUser = Auth::user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get postmen assigned to this PM's location only
        $postmenQuery = User::with(['location'])
            ->where('role', 'postman')
            ->where('location_id', $currentUser->location_id);

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $postmenQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $postmen = $postmenQuery->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pm.postmen', compact('postmen'));
    }

    public function createPostman()
    {
        $currentUser = Auth::user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get only the PM's assigned location
        $locations = Location::where('id', $currentUser->location_id)->get();

        return view('pm.create-postman', compact('locations'));
    }

    public function storePostman(Request $request)
    {
        $currentUser = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'nic' => 'required|string|max:12|unique:users',
            'mobile' => 'required|string|max:15',
            'address' => 'required|string|max:500',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create postman with PM's location only
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nic' => $request->nic,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'role' => 'postman',
            'location_id' => $currentUser->location_id, // Assign to PM's location
            'is_active' => true,
        ]);

        return redirect()->route('pm.postmen.index')->with('success', 'Postman created successfully!');
    }

    public function toggleUserStatus(User $user)
    {
        $currentUser = Auth::user();

        // Ensure the user being toggled belongs to the PM's location
        if ($user->location_id !== $currentUser->location_id) {
            return redirect()->back()->with('error', 'You can only manage users in your assigned location.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "User has been {$status} successfully!");
    }

    public function viewCustomerUpload($id)
    {
        $currentUser = Auth::user();

        // Get the specific upload with all its items for PM's location
        $upload = \App\Models\TemporaryUpload::with(['associates', 'location', 'user'])
            ->where('location_id', $currentUser->location_id)
            ->findOrFail($id);

        // Get service types mapping
        $serviceTypeLabels = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD'
        ];

        return view('pm.view-customer-upload', compact('upload', 'serviceTypeLabels'));
    }

    public function smsLog(Request $request)
    {
        $smsLogs = SmsSent::orderBy('created_at', 'desc')->paginate(20);

        return view('pm.sms-log', compact('smsLogs'));
    }

    public function bulkUpload()
    {
        /** @var User $user */
        $user = Auth::user();

        // Service types for PM uploads (removed remittance)
        $serviceTypes = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD'
        ];

        return view('pm.bulk-upload', compact('user', 'serviceTypes'));
    }

    public function storeBulkUpload(Request $request)
    {
        $request->validate([
            'service_type' => 'required|string|in:register_post,slp_courier,cod',
            'bulk_file' => 'required|file|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Use PM's assigned location as origin post office
        $originLocationId = $user->location_id;

        // Store the uploaded file
        $file = $request->file('bulk_file');

        // Validate file extension more robustly
        $fileExtension = strtolower($file->getClientOriginalExtension());

        // Check if file is CSV
        if (!in_array($fileExtension, ['csv'])) {
            return redirect()->back()->withErrors([
                'bulk_file' => 'Only CSV files are supported. Please save your file as CSV format. In Excel: File > Save As > CSV (Comma delimited).'
            ]);
        }

        $filename = time() . '_PM_' . $file->getClientOriginalName();
        $file->storeAs('bulk_uploads', $filename, 'public');

        // Additional validation for Excel files that might have been renamed
        $csvPath = $file->getPathname();
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            return redirect()->back()->withErrors([
                'bulk_file' => 'Unable to read the uploaded file. Please ensure it is a valid CSV file.'
            ]);
        }

        // Read first few bytes to check if it's actually an Excel file in disguise
        $firstBytes = fread($handle, 4);
        fclose($handle);

        // Check for Excel file signatures
        if (substr($firstBytes, 0, 2) === 'PK' || substr($firstBytes, 0, 4) === "\xD0\xCF\x11\xE0") {
            return redirect()->back()->withErrors([
                'bulk_file' => 'The uploaded file appears to be an Excel file renamed as CSV. Please properly save as CSV format in Excel: File > Save As > CSV (Comma delimited).'
            ]);
        }

        // Parse CSV and create items directly (PM uploads go straight to final tables)
        $csvPath = $file->getPathname();
        $defaultServiceType = $request->service_type;
        $itemsCreated = 0;
        $skippedRows = 0;
        $errors = [];
        $originPostOffice = Location::find($originLocationId);

        DB::beginTransaction();
        try {
            if (($handle = fopen($csvPath, 'r')) !== false) {
                $header = fgetcsv($handle);

                // Clean header
                $header = array_filter(array_map('trim', $header), function($value) {
                    return $value !== '';
                });

                // Check if we have the required columns
                $requiredColumns = ['receiver_name'];
                $recommendedColumns = ['receiver_address', 'contact_number', 'weight', 'item_value', 'service_type'];
                $missingRequired = [];

                foreach ($requiredColumns as $required) {
                    if (!in_array($required, $header)) {
                        $missingRequired[] = $required;
                    }
                }

                if (!empty($missingRequired)) {
                    fclose($handle);
                    DB::rollback();
                    return back()->withErrors([
                        'bulk_file' => 'Missing required columns: ' . implode(', ', $missingRequired) .
                                     '. Found columns: ' . implode(', ', $header) .
                                     '. Required: ' . implode(', ', $requiredColumns) .
                                     '. Optional: ' . implode(', ', $recommendedColumns)
                    ]);
                }

                $rowNumber = 1; // Start from 1 (excluding header)
                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        $skippedRows++;
                        continue;
                    }

                    // Ensure row has same number of elements as header
                    $row = array_slice($row, 0, count($header));
                    if (count($row) < count($header)) {
                        $row = array_pad($row, count($header), '');
                    }

                    $item = array_combine($header, $row);

                    // Skip rows without receiver name
                    if (empty(trim($item['receiver_name'] ?? ''))) {
                        $skippedRows++;
                        $errors[] = "Row $rowNumber: Missing receiver_name";
                        continue;
                    }

                    // Use service type from CSV if provided, otherwise use the selected default
                    $serviceType = $item['service_type'] ?? $defaultServiceType;

                    // Validate service type (removed remittance)
                    if (!in_array($serviceType, ['register_post', 'slp_courier', 'cod'])) {
                        $serviceType = $defaultServiceType;
                    }

                    // Auto-calculate postage based on weight and service type
                    $weight = floatval($item['weight'] ?? 0);
                    $postage = 0;

                    if ($weight > 0) {
                        if ($serviceType === 'slp_courier') {
                            // SLP pricing: Rs. 200 per 250g
                            $postage = ceil($weight / 250) * 200;
                        } elseif ($serviceType === 'register_post') {
                            // Register Post pricing: Rs. 250 per 250g
                            $postage = ceil($weight / 250) * 250;
                        } elseif ($serviceType === 'cod') {
                            // COD pricing: Rs. 290 per 250g
                            $postage = ceil($weight / 250) * 290;
                        }
                    }

                    // Use postage from CSV if provided, otherwise use calculated
                    $finalPostage = !empty($item['postage']) ? floatval($item['postage']) : $postage;

                    // Generate barcode if not provided
                    $barcode = !empty($item['barcode']) ? $item['barcode'] :
                              strtoupper($serviceType === 'slp_courier' ? 'SLP' :
                                        ($serviceType === 'register_post' ? 'REG' : 'COD')) .
                              time() . str_pad($itemsCreated + 1, 4, '0', STR_PAD_LEFT);

                    // Create ItemBulk record first
                    $itemBulk = ItemBulk::create([
                        'sender_name' => trim($item['sender_name'] ?? $user->name),
                        'service_type' => $serviceType,
                        'location_id' => $originLocationId,
                        'created_by' => $user->id,
                        'category' => 'bulk_list', // PM uploads use 'bulk_list' category
                        'item_quantity' => 1,
                        'notes' => trim($item['notes'] ?? ''),
                    ]);

                    // Create Item record directly (auto-accepted for PM uploads)
                    $newItem = Item::create([
                        'item_bulk_id' => $itemBulk->id,
                        'barcode' => $barcode,
                        'receiver_name' => trim($item['receiver_name']),
                        'receiver_address' => trim($item['receiver_address'] ?? ''),
                        'contact_number' => trim($item['contact_number'] ?? ''),
                        'status' => 'accepted', // PM uploads are automatically accepted
                        'weight' => $weight,
                        'amount' => floatval($item['item_value'] ?? 0),
                        'postage' => $finalPostage,
                        'service_type' => $serviceType,
                        'origin_post_office_id' => $originLocationId,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    // Log SMS notification for PM bulk upload acceptance
                    SmsSent::create([
                        'item_id' => $newItem->id,
                        'sender_mobile' => $user->mobile ?? '',
                        'receiver_mobile' => trim($item['contact_number'] ?? ''),
                        'status' => 'accept', // PM uploads are auto-accepted
                    ]);

                    $itemsCreated++;
                }
                fclose($handle);
            }

            DB::commit();

            $message = "PM Bulk upload successful! Created {$itemsCreated} items with service type: " . ucfirst(str_replace('_', ' ', $defaultServiceType)) . ".";

            if ($skippedRows > 0) {
                $message .= " Skipped {$skippedRows} rows.";
            }

            if (!empty($errors) && count($errors) <= 10) {
                $message .= " Issues: " . implode('; ', array_slice($errors, 0, 10));
            }

            return redirect()->route('pm.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['bulk_file' => 'Error processing file: ' . $e->getMessage()]);
        }
    }

    public function showBulkUploadTemplate()
    {
        return response()->download(public_path('templates/pm-bulk-upload-template.csv'));
    }
}
