<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use App\Models\SmsSent;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\Item;
use App\Models\ItemBulk;
use App\Models\Receipt;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PMDashboardController extends Controller
{
    public function index()
    {
        $currentUser = Auth::guard('pm')->user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get customer statistics for PM's location only
        $customerUsers = User::where('role', 'customer')
                            ->where('location_id', $currentUser->location_id)
                            ->count();
        $activeCustomers = User::where('role', 'customer')
                               ->where('location_id', $currentUser->location_id)
                               ->where('is_active', true)
                               ->count();
        $externalCustomers = User::where('role', 'external_customer')
                                ->where('location_id', $currentUser->location_id)
                                ->count();

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

        return view('pm.modern-dashboard', compact(
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
        $currentUser = Auth::guard('pm')->user();

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

        return view('pm.customers.modern-index', compact('customers'));
    }

    public function createCustomer()
    {
        $currentUser = Auth::guard('pm')->user();

        // Ensure user is authenticated and has location_id
        if (!$currentUser || !$currentUser->location_id) {
            return redirect()->route('pm.login')->with('error', 'Please login to access the dashboard.');
        }

        // Get only the PM's assigned location
        $locations = Location::where('id', $currentUser->location_id)->get();

        return view('pm.customers.modern-create', compact('locations'));
    }

    public function storeCustomer(Request $request)
    {
        $currentUser = Auth::guard('pm')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:12|unique:users',
            'company_name' => 'nullable|string|max:255',
            'company_br' => 'nullable|string|max:50',
            'email' => 'nullable|string|email|max:255|unique:users',
            'mobile' => 'required|string|max:15',
            'address' => 'required|string|max:500',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create customer with PM's location only
        User::create([
            'name' => $request->name,
            'nic' => $request->nic,
            'company_name' => $request->company_name,
            'company_br' => $request->company_br,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'password' => bcrypt($request->password),
            'user_type' => 'external',
            'role' => 'customer',
            'location_id' => $currentUser->location_id, // Assign to PM's location
            'is_active' => true,
        ]);

        return redirect()->route('pm.customers.index')->with('success', 'Customer created successfully! Customer can now login to their account.');
    }

    public function customerUploads(Request $request)
    {
        $currentUser = Auth::guard('pm')->user();

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

    public function toggleUserStatus(User $user)
    {
        $currentUser = Auth::guard('pm')->user();

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
        $currentUser = Auth::guard('pm')->user();

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

    public function bulkUpload()
    {
        /** @var User $user */
        $user = Auth::guard('pm')->user();

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
        $user = Auth::guard('pm')->user();

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
                $validItems = []; // Store valid items for bulk creation

                // First pass: Validate and collect all valid items
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

                    $validItems[] = [
                        'data' => $item,
                        'service_type' => $serviceType,
                        'row_number' => $rowNumber
                    ];
                }

                // If no valid items, rollback
                if (empty($validItems)) {
                    fclose($handle);
                    DB::rollback();
                    return back()->withErrors(['bulk_file' => 'No valid items found in CSV file.']);
                }

                // Create single ItemBulk record for the entire bulk upload
                $itemBulk = ItemBulk::create([
                    'sender_name' => $user->name, // PM name as sender
                    'service_type' => $defaultServiceType,
                    'location_id' => $originLocationId,
                    'created_by' => $user->id,
                    'category' => 'bulk_list', // PM uploads use 'bulk_list' category
                    'item_quantity' => count($validItems),
                ]);

                $totalAmount = 0;

                // Second pass: Create all items linked to the single ItemBulk
                foreach ($validItems as $validItem) {
                    $item = $validItem['data'];
                    $serviceType = $validItem['service_type'];

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

                    // Create Item record linked to the single ItemBulk
                    $newItem = Item::create([
                        'item_bulk_id' => $itemBulk->id,
                        'barcode' => $barcode,
                        'receiver_name' => trim($item['receiver_name']),
                        'receiver_address' => trim($item['receiver_address'] ?? ''),
                        'contact_number' => trim($item['contact_number'] ?? ''),
                        'status' => 'accept', // PM uploads are automatically accepted
                        'weight' => $weight,
                        'amount' => floatval($item['item_value'] ?? 0),
                        'postage' => $finalPostage,
                        'service_type' => $serviceType,
                        'origin_post_office_id' => $originLocationId,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    // Create Payment record for COD items in PM bulk upload
                    if ($serviceType === 'cod' && floatval($item['item_value'] ?? 0) > 0) {
                        Payment::create([
                            'item_id' => $newItem->id,
                            'fixed_amount' => floatval($item['item_value'] ?? 0),
                            'commission' => floatval($item['commission'] ?? 0),
                            'item_value' => floatval($item['item_value'] ?? 0),
                            'status' => 'accept',
                        ]);
                    }

                    // Create SMS notification for each item
                    SmsSent::create([
                        'item_id' => $newItem->id,
                        'sender_mobile' => $user->mobile ?? '',
                        'receiver_mobile' => trim($item['contact_number'] ?? ''),
                        'status' => 'accept', // PM uploads are auto-accepted
                    ]);

                    $totalAmount += floatval($item['item_value'] ?? 0);
                    $itemsCreated++;
                }

                // Create single Receipt for the entire bulk upload
                $receipt = Receipt::create([
                    'item_quantity' => $itemsCreated,
                    'item_bulk_id' => $itemBulk->id,
                    'amount' => $totalAmount, // Total amount of all items
                    'payment_type' => 'cash',
                    'created_by' => $user->id,
                    'location_id' => $originLocationId,
                    'passcode' => $this->generatePasscode()
                ]);
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

    public function acceptAllUpload($uploadId)
    {
        DB::beginTransaction();
        try {
            $temporaryUpload = TemporaryUpload::findOrFail($uploadId);

            // Get all pending items with barcodes from this upload
            $pendingItems = TemporaryUploadAssociate::where('temporary_id', $temporaryUpload->id)
                ->where('status', 'pending')
                ->whereNotNull('barcode')
                ->where('barcode', '!=', '')
                ->get();

            if ($pendingItems->isEmpty()) {
                return back()->with('warning', 'No pending items with barcodes found to accept.');
            }

            $currentUser = Auth::guard('pm')->user();
            $acceptedCount = 0;

            // ALWAYS create NEW ItemBulk for each acceptance session
            // This ensures proper sequential ItemBulk IDs and no reuse of old records
            // PRESERVE ORIGINAL CATEGORY from the temporary upload
            $itemBulk = ItemBulk::create([
                'sender_name' => $temporaryUpload->user->name,
                'service_type' => $pendingItems->first()->service_type ?? 'register_post',
                'location_id' => $temporaryUpload->location_id,
                'created_by' => $currentUser->id,
                'category' => $temporaryUpload->category, // FIXED: Use original category instead of hardcoded 'temporary_list'
                'item_quantity' => $pendingItems->count(),
            ]);

            foreach ($pendingItems as $tempItem) {
                // Create item in items table
                $item = Item::create([
                    'item_bulk_id' => $itemBulk->id,
                    'barcode' => $tempItem->barcode,
                    'receiver_name' => $tempItem->receiver_name,
                    'receiver_address' => $tempItem->receiver_address,
                    'status' => 'accept',
                    'weight' => $tempItem->weight,
                    'amount' => $tempItem->service_type === 'cod' ? $tempItem->amount : 0.00,
                    'created_by' => $currentUser->id,
                    'updated_by' => $currentUser->id,
                ]);

                // Create Payment record for COD items
                if ($tempItem->service_type === 'cod' && $tempItem->amount > 0) {
                    Payment::create([
                        'item_id' => $item->id,
                        'fixed_amount' => $tempItem->amount,
                        'commission' => $tempItem->commission ?? 0.00,
                        'item_value' => $tempItem->item_value ?? $tempItem->amount,
                        'status' => 'accept',
                    ]);
                }

                // Create SMS notification for accepted item
                SmsSent::create([
                    'item_id' => $item->id,
                    'sender_mobile' => $temporaryUpload->user->mobile ?? '',
                    'receiver_mobile' => $tempItem->contact_number ?? '',
                    'status' => 'accept',
                ]);

                // Update the temporary upload associate status
                $tempItem->update(['status' => 'accept']);

                $acceptedCount++;
            }

            // Create receipt for accepted items (all items should have receipts)
            $allItems = $itemBulk->items;
            if ($allItems->count() > 0) {
                // Calculate amounts based on service type logic
                $codAmount = $allItems->sum('amount'); // COD amounts from items
                $postageAmount = $pendingItems->sum('postage'); // Postage from temp uploads

                // Total calculation based on service types:
                // - COD: postage + amount
                // - SLP Courier/Register Post: postage only (no COD amount)
                $totalAmount = $codAmount + $postageAmount;

                Receipt::create([
                    'item_quantity' => $allItems->count(),
                    'item_bulk_id' => $itemBulk->id,
                    'amount' => $codAmount, // COD amount only
                    'postage' => $postageAmount, // Postage fees
                    'total_amount' => $totalAmount, // Combined total
                    'payment_type' => 'cash',
                    'passcode' => $this->generatePasscode(),
                    'created_by' => $currentUser->id,
                    'location_id' => $temporaryUpload->location_id,
                ]);
            }

            DB::commit();

            return back()->with('success', "Successfully accepted {$acceptedCount} items from upload #{$temporaryUpload->id} into ItemBulk #{$itemBulk->id}. " .
                                         "Receipt created for all accepted items.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error accepting items: ' . $e->getMessage()]);
        }
    }

    public function acceptSelectedUpload(Request $request, $uploadId)
    {
        $request->validate([
            'selected_items' => 'required|array|min:1',
            'selected_items.*' => 'integer|exists:temporary_upload_associates,id'
        ]);

        DB::beginTransaction();
        try {
            $temporaryUpload = TemporaryUpload::findOrFail($uploadId);

            // Get selected pending items with barcodes from this upload
            $selectedItems = TemporaryUploadAssociate::where('temporary_id', $temporaryUpload->id)
                ->whereIn('id', $request->selected_items)
                ->where('status', 'pending')
                ->whereNotNull('barcode')
                ->where('barcode', '!=', '')
                ->get();

            if ($selectedItems->isEmpty()) {
                return back()->with('warning', 'No valid items selected for acceptance.');
            }

            $currentUser = Auth::guard('pm')->user();
            $acceptedCount = 0;

            // ALWAYS create NEW ItemBulk for each acceptance session
            // This ensures proper sequential ItemBulk IDs and no reuse of old records
            // PRESERVE ORIGINAL CATEGORY from the temporary upload
            $itemBulk = ItemBulk::create([
                'sender_name' => $temporaryUpload->user->name,
                'service_type' => $selectedItems->first()->service_type ?? 'register_post',
                'location_id' => $temporaryUpload->location_id,
                'created_by' => $currentUser->id,
                'category' => $temporaryUpload->category, // FIXED: Use original category instead of hardcoded 'temporary_list'
                'item_quantity' => $selectedItems->count(),
            ]);

            foreach ($selectedItems as $tempItem) {
                // Create item in items table
                $item = Item::create([
                    'item_bulk_id' => $itemBulk->id,
                    'barcode' => $tempItem->barcode,
                    'receiver_name' => $tempItem->receiver_name,
                    'receiver_address' => $tempItem->receiver_address,
                    'status' => 'accept',
                    'weight' => $tempItem->weight,
                    'amount' => $tempItem->service_type === 'cod' ? $tempItem->amount : 0.00,
                    'created_by' => $currentUser->id,
                    'updated_by' => $currentUser->id,
                ]);

                // Create Payment record for COD items
                if ($tempItem->service_type === 'cod' && $tempItem->amount > 0) {
                    Payment::create([
                        'item_id' => $item->id,
                        'fixed_amount' => $tempItem->amount,
                        'commission' => $tempItem->commission ?? 0.00,
                        'item_value' => $tempItem->item_value ?? $tempItem->amount,
                        'status' => 'accept',
                    ]);
                }

                // Create SMS notification for accepted item
                SmsSent::create([
                    'item_id' => $item->id,
                    'sender_mobile' => $temporaryUpload->user->mobile ?? '',
                    'receiver_mobile' => $tempItem->contact_number ?? '',
                    'status' => 'accept',
                ]);

                // Update the temporary upload associate status
                $tempItem->update(['status' => 'accept']);

                $acceptedCount++;
            }

            // Create receipt for accepted items (all items should have receipts)
            $allItems = $itemBulk->items;
            if ($allItems->count() > 0) {
                // Calculate amounts based on service type logic
                $codAmount = $allItems->sum('amount'); // COD amounts from items
                $postageAmount = $selectedItems->sum('postage'); // Postage from selected temp uploads

                // Total calculation based on service types:
                // - COD: postage + amount
                // - SLP Courier/Register Post: postage only (no COD amount)
                $totalAmount = $codAmount + $postageAmount;

                Receipt::create([
                    'item_quantity' => $allItems->count(),
                    'item_bulk_id' => $itemBulk->id,
                    'amount' => $codAmount, // COD amount only
                    'postage' => $postageAmount, // Postage fees
                    'total_amount' => $totalAmount, // Combined total
                    'payment_type' => 'cash',
                    'passcode' => $this->generatePasscode(),
                    'created_by' => $currentUser->id,
                    'location_id' => $temporaryUpload->location_id,
                ]);
            }

            DB::commit();

            return back()->with('success', "Successfully accepted {$acceptedCount} selected items from upload #{$temporaryUpload->id} into ItemBulk #{$itemBulk->id}. " .
                                         "Receipt created for all accepted items.");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Error accepting selected items: ' . $e->getMessage()]);
        }
    }

    public function showBulkUploadTemplate()
    {
        return response()->download(public_path('templates/pm-bulk-upload-template.csv'));
    }

    /**
     * View receipt for accepted customer upload
     */
    public function viewCustomerUploadReceipt($uploadId)
    {
        $currentUser = Auth::guard('pm')->user();

        // Find the temporary upload for PM's location
        $upload = \App\Models\TemporaryUpload::with(['user', 'location'])
            ->where('location_id', $currentUser->location_id)
            ->findOrFail($uploadId);

        // Find accepted items from this upload by matching barcodes
        $acceptedAssociates = \App\Models\TemporaryUploadAssociate::where('temporary_id', $upload->id)
            ->where('status', 'accept')
            ->whereNotNull('barcode')
            ->get();

        if ($acceptedAssociates->isEmpty()) {
            return back()->with('error', 'No accepted items found for this upload. Please accept some items first.');
        }

        // Find items that match these barcodes
        $barcodes = $acceptedAssociates->pluck('barcode')->filter();
        $items = \App\Models\Item::whereIn('barcode', $barcodes)->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'No items found in system for accepted uploads.');
        }

        // Get the ItemBulk from the first item (they should all belong to the same bulk)
        $itemBulk = $items->first()->itemBulk;

        if (!$itemBulk) {
            return back()->with('error', 'No ItemBulk found for accepted items.');
        }

        // Find the receipt for this ItemBulk
        $receipt = \App\Models\Receipt::where('item_bulk_id', $itemBulk->id)->first();

        if (!$receipt) {
            return back()->with('error', 'No receipt found for this upload. Receipt may not have been created.');
        }

        // Load relationships for the receipt
        $receipt->load([
            'itemBulk.creator.location',
            'itemBulk.items.smsSents',
            'location'
        ]);

        return view('pm.customer-upload-receipt', compact('receipt', 'upload'));
    }

    /**
     * Print receipt for accepted customer upload
     */
    public function printCustomerUploadReceipt($uploadId)
    {
        $currentUser = Auth::guard('pm')->user();

        // Find the temporary upload for PM's location
        $upload = \App\Models\TemporaryUpload::with(['user', 'location'])
            ->where('location_id', $currentUser->location_id)
            ->findOrFail($uploadId);

        // Find accepted items from this upload by matching barcodes
        $acceptedAssociates = \App\Models\TemporaryUploadAssociate::where('temporary_id', $upload->id)
            ->where('status', 'accept')
            ->whereNotNull('barcode')
            ->get();

        if ($acceptedAssociates->isEmpty()) {
            return back()->with('error', 'No accepted items found for this upload.');
        }

        // Find items that match these barcodes
        $barcodes = $acceptedAssociates->pluck('barcode')->filter();
        $items = \App\Models\Item::whereIn('barcode', $barcodes)->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'No items found in system for accepted uploads.');
        }

        // Get the ItemBulk from the first item
        $itemBulk = $items->first()->itemBulk;

        if (!$itemBulk) {
            return back()->with('error', 'No ItemBulk found for accepted items.');
        }

        // Find the receipt for this ItemBulk
        $receipt = \App\Models\Receipt::where('item_bulk_id', $itemBulk->id)->first();

        if (!$receipt) {
            return back()->with('error', 'No receipt found for this upload.');
        }

        // Load relationships for the receipt
        $receipt->load([
            'itemBulk.creator.location',
            'itemBulk.items.smsSents',
            'location'
        ]);

        return view('pm.customer-upload-print-receipt', compact('receipt', 'upload'));
    }

    /**
     * Generate a 6-digit passcode for receipts
     */
    private function generatePasscode()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
