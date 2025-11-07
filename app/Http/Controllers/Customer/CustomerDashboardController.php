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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();

        // Load user with location relationship
        $user = User::with('location')->find($user->id);

        // Get user statistics - customer items are submitted through TemporaryUpload
        // and then become ItemBulk when accepted/rejected by PM
        $temporaryUploads = TemporaryUpload::where('user_id', $user->id)->with('associates')->get();

        $totalItems = 0;
        $pendingItems = 0;
        $acceptedItems = 0;
        $rejectedItems = 0;

        foreach ($temporaryUploads as $upload) {
            $itemCount = $upload->associates->count();
            $totalItems += $itemCount;

            if ($upload->status === 'pending') {
                $pendingItems += $itemCount;
            } elseif ($upload->status === 'accept') {
                $acceptedItems += $itemCount;
            } elseif ($upload->status === 'reject') {
                $rejectedItems += $itemCount;
            }
        }

        // Get recent uploads
        $recentUploads = TemporaryUpload::where('user_id', $user->id)
            ->with('associates')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get service type breakdown from associates
        $serviceBreakdown = [];
        foreach ($temporaryUploads as $upload) {
            foreach ($upload->associates as $associate) {
                $serviceType = $associate->service_type;
                if (!isset($serviceBreakdown[$serviceType])) {
                    $serviceBreakdown[$serviceType] = 0;
                }
                $serviceBreakdown[$serviceType]++;
            }
        }

        return view('customer.dashboard', compact('user', 'totalItems', 'pendingItems', 'acceptedItems', 'rejectedItems', 'recentUploads', 'serviceBreakdown'));
    }

    public function profile()
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();
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
        $user = Auth::guard('customer')->user();
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
        $user = Auth::guard('customer')->user();

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
        $user = Auth::guard('customer')->user();

        // Get statistics from TemporaryUploadAssociate table which matches the items view
        $totalItems = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->count();

        $pendingItems = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'pending')->count();

        $acceptedItems = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'accept')->count();

        $rejectedItems = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('status', 'reject')->count();

        return view('customer.services.index', compact('user', 'totalItems', 'pendingItems', 'acceptedItems', 'rejectedItems'));
    }

    public function addSingleItem()
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();
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
            ]
        ];

        return view('customer.services.add-single-item', compact('user', 'locations', 'serviceTypes'));
    }

    public function storeSingleItem(Request $request)
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();

        // Validation rules for items
        $rules = [
            'receiver_name' => 'required|string|max:255',
            'receiver_mobile' => 'required|string|max:15', // Add receiver mobile validation
            'address' => 'required|string',
            'weight' => 'required|numeric|min:0',
            'service_type' => 'required|in:register_post,slp_courier,cod',
            'origin_post_office_id' => 'required|exists:locations,id',
            'barcode' => 'nullable|string|max:255', // Optional barcode field
        ];

        // Item value and amount are only required for COD
        if (in_array($request->service_type, ['cod'])) {
            $rules['amount'] = 'required|numeric|min:0';
            $rules['item_value'] = 'required|numeric|min:0';
        } else {
            $rules['amount'] = 'nullable|numeric|min:0';
            $rules['item_value'] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        // Create temporary upload record
        $temporaryUpload = TemporaryUpload::create([
            'category' => 'single_item',
            'location_id' => $request->origin_post_office_id,
            'user_id' => $user->id,
        ]);

        // Calculate postage (using default service type since service_type was removed)
        $postage = $this->calculatePostageInternal($request->service_type ?? 'register_post', $request->weight, $request->amount);

        // Create temporary upload associate record with item details
        $temporaryAssociate = TemporaryUploadAssociate::create([
            'temporary_id' => $temporaryUpload->id,
            'sender_name' => $user->name,
            'receiver_name' => $request->receiver_name,
            'contact_number' => $request->receiver_mobile, // Store receiver mobile in contact_number field
            'receiver_address' => $request->address,
            'weight' => $request->weight,
            'amount' => $request->amount ?? 0, // Default to 0 if not provided
            'item_value' => $request->service_type === 'cod' ? $request->item_value : 0, // Only COD has item value
            'service_type' => $request->service_type, // Store service type directly
            'barcode' => $request->barcode, // Store customer-provided barcode
            'postage' => $postage,
            'commission' => 0, // Default commission
            'fix_amount' => null, // No fix amount for single items
            'status' => 'pending', // Status is pending until PM accepts
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

    public function showBulkUploadTemplate()
    {
        return response()->download(public_path('templates/customer-bulk-upload-template.csv'));
    }

    /**
     * Parse and validate numeric values from CSV data
     */
    private function parseNumericValue($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        // Remove any non-numeric characters except decimal point and negative sign
        $cleaned = preg_replace('/[^0-9.-]/', '', $value);

        // Check if the result is a valid number
        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }

        // If not numeric, return 0
        return 0;
    }

    public function bulkUpload()
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();
        $locations = Location::active()->get();

        // For bulk upload, we just need simple key-value pairs
        $serviceTypes = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD'
        ];

        return view('customer.services.bulk-upload', compact('user', 'locations', 'serviceTypes'));
    }

    public function storeBulkUpload(Request $request)
    {
        // Enhanced debugging
        Log::info('=== BULK UPLOAD REQUEST DEBUG ===');
        Log::info('Request method: ' . $request->method());
        Log::info('Has file: ' . ($request->hasFile('bulk_file') ? 'YES' : 'NO'));
        if ($request->hasFile('bulk_file')) {
            $file = $request->file('bulk_file');
            Log::info('File original name: ' . $file->getClientOriginalName());
            Log::info('File extension: ' . $file->getClientOriginalExtension());
            Log::info('File MIME type: ' . $file->getMimeType());
            Log::info('File size: ' . $file->getSize());
            Log::info('File is valid: ' . ($file->isValid() ? 'YES' : 'NO'));
        }
        Log::info('All request data: ', $request->all());

        $request->validate([
            'origin_post_office_id' => 'required|exists:locations,id',
            'service_type' => 'required|string|in:register_post,slp_courier,cod',
            'bulk_file' => 'required|file|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::guard('customer')->user();

        // Store the uploaded file
        $file = $request->file('bulk_file');

        // Validate file extension more robustly
        $fileExtension = strtolower($file->getClientOriginalExtension());

        Log::info('File extension check: ' . $fileExtension);

        // Check if file is CSV
        if (!in_array($fileExtension, ['csv'])) {
            Log::info('File rejected - not CSV extension');
            return redirect()->back()->withErrors([
                'bulk_file' => 'Only CSV files are supported. Please save your file as CSV format. In Excel: File > Save As > CSV (Comma delimited).'
            ]);
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('bulk_uploads', $filename, 'public');

        // Additional validation for Excel files that might have been renamed
        $csvPath = $file->getPathname();
        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            Log::info('File rejected - cannot open for reading');
            return redirect()->back()->withErrors([
                'bulk_file' => 'Unable to read the uploaded file. Please ensure it is a valid CSV file.'
            ]);
        }

        // Read first few bytes to check if it's actually an Excel file in disguise
        $firstBytes = fread($handle, 4);
        fclose($handle);

        Log::info('First 4 bytes of file: ' . bin2hex($firstBytes));

        // Check for Excel file signatures
        if (substr($firstBytes, 0, 2) === 'PK' || substr($firstBytes, 0, 4) === "\xD0\xCF\x11\xE0") {
            Log::info('File rejected - Excel file signature detected');
            return redirect()->back()->withErrors([
                'bulk_file' => 'The uploaded file appears to be an Excel file renamed as CSV. Please properly save as CSV format in Excel: File > Save As > CSV (Comma delimited).'
            ]);
        }

        // Use database transaction to ensure data integrity
        try {
            DB::beginTransaction();

            // Create temporary upload record (customer bulk uploads always use 'temporary_list' category)
            $temporaryUpload = TemporaryUpload::create([
                'category' => 'temporary_list',
                'location_id' => $request->origin_post_office_id,
                'user_id' => $user->id,
            ]);

            Log::info('TemporaryUpload created with ID: ' . $temporaryUpload->id);

            // Parse CSV and store each item
            $csvPath = $file->getPathname();
            $items = [];
            $itemCount = 0; // Track total items processed
            $defaultServiceType = $request->service_type; // Service type selected by user

            // Debug: Log file info
            Log::info('=== FILE UPLOAD DEBUG ===');
            Log::info('Original filename: ' . $file->getClientOriginalName());
            Log::info('File extension: ' . $fileExtension);
            Log::info('File size: ' . $file->getSize());
            Log::info('File path: ' . $csvPath);
            Log::info('Processing CSV file');

            if (($handle = fopen($csvPath, 'r')) !== false) {
                // Read first line as header
                $header = fgetcsv($handle);

                if ($header === false || empty($header)) {
                    Log::error('Failed to read CSV header or header is empty');
                    fclose($handle);
                    DB::rollback();
                    return redirect()->back()->withErrors(['bulk_file' => 'Unable to read file header. Please check file format.']);
                }

                // Clean header (remove empty columns and trim whitespace)
                $originalHeader = $header;
                $header = array_filter(array_map('trim', $header), function($value) {
                    return $value !== '';
                });

                // Debug: Log the exact CSV headers found
                Log::info('=== CSV HEADER ANALYSIS ===');
                Log::info('Original header (raw): ', $originalHeader);
                Log::info('Cleaned header: ', $header);
                Log::info('Header count: ' . count($header));

                if (empty($header)) {
                    Log::error('No valid headers found after cleaning');
                    fclose($handle);
                    DB::rollback();
                    return redirect()->back()->withErrors(['bulk_file' => 'No valid column headers found. Please check your file format.']);
                }

                // Create a mapping for common header variations
                $headerMapping = [];
                foreach ($header as $index => $columnName) {
                    $cleanName = strtolower(trim($columnName));
                    $cleanName = str_replace([' ', '_', '-'], '_', $cleanName);

                    // Map common variations to standard names
                if (in_array($cleanName, ['receiver_name', 'receivername', 'receiver', 'name', 'recipient_name', 'recipient'])) {
                    $headerMapping['receiver_name'] = $index;
                } elseif (in_array($cleanName, ['receiver_address', 'receiveraddress', 'address', 'recipient_address'])) {
                    $headerMapping['receiver_address'] = $index;
                } elseif (in_array($cleanName, ['item_value', 'itemvalue', 'value', 'amount', 'price'])) {
                    $headerMapping['item_value'] = $index;
                } elseif (in_array($cleanName, ['weight', 'weight_grams', 'wt'])) {
                    $headerMapping['weight'] = $index;
                } elseif (in_array($cleanName, ['postage', 'postal_charge', 'shipping'])) {
                    $headerMapping['postage'] = $index;
                } elseif (in_array($cleanName, ['barcode', 'bar_code', 'code', 'tracking_code', 'track_code'])) {
                    $headerMapping['barcode'] = $index;
                } elseif (in_array($cleanName, ['contact_number', 'contactnumber', 'contact', 'phone', 'mobile', 'telephone'])) {
                    $headerMapping['contact_number'] = $index;
                } elseif (in_array($cleanName, ['destination_location', 'destination', 'dest_location', 'delivery_office'])) {
                    $headerMapping['destination_location'] = $index;
                } elseif (in_array($cleanName, ['service_type', 'servicetype', 'type', 'service'])) {
                    $headerMapping['service_type'] = $index;
                } elseif (in_array($cleanName, ['notes', 'note', 'comments', 'comment', 'remarks'])) {
                    $headerMapping['notes'] = $index;
                }
                }

                Log::info('Header mapping created: ', $headerMapping);

                $totalRowsRead = 0;
                $emptyRowsSkipped = 0;
                $noReceiverNameSkipped = 0;
                $databaseErrorsSkipped = 0;
                $databaseErrors = [];

                while (($row = fgetcsv($handle)) !== false) {
                    $totalRowsRead++;

                    // Skip empty rows - more robust checking
                    $filteredRow = array_filter($row, function($cell) {
                        return trim($cell) !== '';
                    });

                    if (empty($filteredRow)) {
                        $emptyRowsSkipped++;
                        Log::info("Skipping empty row #{$totalRowsRead}: ", $row);
                        continue;
                    }

                // Ensure row has same number of elements as header
                $row = array_slice($row, 0, count($header));
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                // Map the row data using our header mapping
                $mappedItem = [];
                foreach ($headerMapping as $standardField => $csvIndex) {
                    $mappedItem[$standardField] = isset($row[$csvIndex]) ? trim($row[$csvIndex]) : '';
                }

                // Also create the original item array for backwards compatibility
                $item = array_combine($header, $row);

                // Additional validation: Only process rows that have at least a receiver name
                $receiverName = $mappedItem['receiver_name'] ?? $item['receiver_name'] ??
                               $item['Receiver Name'] ?? $item['name'] ?? $item['Name'] ??
                               $item['receiver'] ?? $item['Receiver'] ?? '';

                if (trim($receiverName) === '') {
                    $noReceiverNameSkipped++;
                    Log::info("Skipping row #{$totalRowsRead} without receiver name: ", $item);
                    continue;
                }

                // Debug: Log first 3 rows of data for inspection
                if ($itemCount < 3) {
                    Log::info("Processing row " . ($itemCount + 1) . " (CSV row #{$totalRowsRead}):");
                    Log::info("  Original data: ", $item);
                    Log::info("  Mapped data: ", $mappedItem);
                    Log::info("  Receiver name found: '{$receiverName}'");
                }

                // Use service type from CSV if provided, otherwise use the selected default
                $serviceType = $mappedItem['service_type'] ?? $item['service_type'] ?? $defaultServiceType;

                // Validate service type
                if (!in_array($serviceType, ['register_post', 'slp_courier', 'cod'])) {
                    $serviceType = $defaultServiceType;
                }

                // Enhanced data extraction with fallback logic (receiverName already extracted above for validation)
                $receiverAddress = $mappedItem['receiver_address'] ?? $item['receiver_address'] ??
                                  $item['Receiver Address'] ?? $item['address'] ?? $item['Address'] ??
                                  $item['receiver_addr'] ?? $item['addr'] ?? '';

                // Only use item_value for COD service type, set to 0 for others
                $itemValue = 0; // Default to 0 for non-COD services
                if ($serviceType === 'cod') {
                    $itemValue = $this->parseNumericValue($mappedItem['item_value'] ?? $item['item_value'] ??
                                $item['Item Value'] ?? $item['value'] ?? $item['Value'] ??
                                $item['amount'] ?? $item['Amount'] ?? 0);
                }

                $weight = $this->parseNumericValue($mappedItem['weight'] ?? $item['weight'] ??
                         $item['Weight'] ?? $item['wt'] ?? $item['Wt'] ?? 0);

                $postage = $this->parseNumericValue($mappedItem['postage'] ?? $item['postage'] ??
                          $item['Postage'] ?? $item['postal_charge'] ?? 0);

                // Get barcode if provided
                $barcode = $mappedItem['barcode'] ?? $item['barcode'] ?? $item['Barcode'] ??
                          $item['bar_code'] ?? $item['Bar Code'] ?? $item['code'] ??
                          $item['Code'] ?? $item['tracking_code'] ?? null;

                // Get contact number if provided
                $contactNumber = $mappedItem['contact_number'] ?? $item['contact_number'] ??
                               $item['Contact Number'] ?? $item['contact'] ?? $item['phone'] ??
                               $item['mobile'] ?? $item['telephone'] ?? null;

                // Use authenticated user as sender (no CSV sender fields needed)
                $senderName = $user->name;
                $senderMobile = $user->mobile ?? null;
                $senderAddress = null; // Customer's address not needed for sender

                // Validate required fields before database insert
                $amount = $itemValue ?? 0; // Use item_value as amount, default to 0 if null
                $postage = $postage ?? 0;  // Default to 0 if null

                // Check for missing required fields
                if (empty($receiverAddress)) {
                    $databaseErrorsSkipped++;
                    $databaseErrors[] = "Row " . ($totalRowsRead) . ": Missing receiver address";
                    Log::error("Skipping row {$totalRowsRead}: Missing receiver address");
                    continue;
                }

                if (empty($senderName)) {
                    $databaseErrorsSkipped++;
                    $databaseErrors[] = "Row " . ($totalRowsRead) . ": Missing sender name";
                    Log::error("Skipping row {$totalRowsRead}: Missing sender name (user name)");
                    continue;
                }

                // Create the temporary upload associate record with service type info
                try {
                    $tempAssociate = TemporaryUploadAssociate::create([
                        'temporary_id' => $temporaryUpload->id,
                        'sender_name' => $senderName, // Always use authenticated user name as sender
                        'receiver_name' => $receiverName,
                        'receiver_address' => $receiverAddress,
                        'item_value' => $itemValue ?? 0,
                        'weight' => $weight,
                        'amount' => $amount, // Ensure not null
                        'postage' => $postage, // Ensure not null
                        'barcode' => $barcode,
                        'contact_number' => $contactNumber,
                        'service_type' => $serviceType, // Store service type directly in temporary table
                        'commission' => $this->parseNumericValue($item['commission'] ?? 0),
                        'fix_amount' => $this->parseNumericValue($item['fix_amount'] ?? null),
                        'status' => 'pending',
                        'notes' => $mappedItem['notes'] ?? $item['notes'] ?? null,
                    ]);

                    Log::info("TemporaryUploadAssociate created with ID: " . $tempAssociate->id);

                } catch (\Exception $e) {
                    $databaseErrorsSkipped++;
                    $databaseErrors[] = "Row " . ($totalRowsRead) . ": " . $e->getMessage();
                    Log::error("Failed to create TemporaryUploadAssociate for row " . ($itemCount + 1) . ": " . $e->getMessage());
                    Log::error("Data that failed: ", [
                        'temporary_id' => $temporaryUpload->id,
                        'receiver_name' => $receiverName,
                        'receiver_address' => $receiverAddress,
                        'item_value' => $itemValue,
                        'weight' => $weight,
                        'postage' => $postage,
                        'barcode' => $barcode,
                        'contact_number' => $contactNumber,
                    ]);
                    continue; // Skip this row and continue with next
                }

                // Debug: Log what was actually saved
                if ($itemCount < 3) {
                    Log::info("Row " . ($itemCount + 1) . " final saved data: ", [
                        'receiver_name' => $receiverName,
                        'receiver_address' => $receiverAddress,
                        'item_value' => $itemValue,
                        'weight' => $weight,
                        'postage' => $postage,
                        'barcode' => $barcode,
                        'contact_number' => $contactNumber,
                        'sender_name' => $senderName,
                        'service_type' => $serviceType,
                    ]);
                }

                $itemCount++; // Increment item counter
                // Note: ItemBulk records will be created when PM accepts the items
            }
            fclose($handle);
        }

            // Final debug log with statistics
            Log::info('=== CSV PROCESSING COMPLETED ===');
            Log::info("Total rows read from file: {$totalRowsRead}");
            Log::info("Empty rows skipped: {$emptyRowsSkipped}");
            Log::info("Rows without receiver name skipped: {$noReceiverNameSkipped}");
            Log::info("Database errors skipped: {$databaseErrorsSkipped}");
            Log::info("Successfully processed items: {$itemCount}");
            Log::info('Upload ID: ' . $temporaryUpload->id);

            // Check if any items were actually processed
            if ($itemCount === 0) {
                DB::rollback();

                $errorMessage = 'No valid data rows were found in your file. ';
                $errorMessage .= "Total rows read: {$totalRowsRead}, ";
                $errorMessage .= "Empty rows skipped: {$emptyRowsSkipped}, ";
                $errorMessage .= "Rows without receiver name: {$noReceiverNameSkipped}";

                if ($databaseErrorsSkipped > 0) {
                    $errorMessage .= ", Database errors: {$databaseErrorsSkipped}";
                    if (!empty($databaseErrors)) {
                        $errorMessage .= " (First few errors: " . implode('; ', array_slice($databaseErrors, 0, 3)) . ")";
                    }
                }

                $errorMessage .= ". Please ensure your CSV has a 'receiver_name' column with valid data.";

                return redirect()->back()->withErrors([
                    'bulk_file' => $errorMessage
                ]);
            }            // Commit the transaction
            DB::commit();

            Log::info('Database transaction committed successfully');

            $successMessage = "File uploaded successfully! Processed {$itemCount} items with service type: " . ucfirst(str_replace('_', ' ', $defaultServiceType)) . ".";
            $successMessage .= " Total rows read: {$totalRowsRead}";

            if ($emptyRowsSkipped > 0) {
                $successMessage .= ", Empty rows skipped: {$emptyRowsSkipped}";
            }
            if ($noReceiverNameSkipped > 0) {
                $successMessage .= ", Rows without receiver name skipped: {$noReceiverNameSkipped}";
            }
            if ($databaseErrorsSkipped > 0) {
                $successMessage .= ", Database validation errors: {$databaseErrorsSkipped}";
            }

            return redirect()->route('customer.services.bulk-status', $temporaryUpload->id)
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Database transaction failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()->withErrors([
                'bulk_file' => 'Failed to save data to database. Please try again. Error: ' . $e->getMessage()
            ]);
        }
    }

    public function items(Request $request)
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();

        // Get uploads grouped by TemporaryUpload with item counts and status summaries
        $uploadsQuery = TemporaryUpload::where('user_id', $user->id)
            ->with(['associates', 'location'])
            ->withCount([
                'associates as total_items',
                'associates as pending_items' => function($query) {
                    $query->where('status', 'pending');
                },
                'associates as accepted_items' => function($query) {
                    $query->where('status', 'accept');
                },
                'associates as rejected_items' => function($query) {
                    $query->where('status', 'reject');
                }
            ]);

        // Apply status filter
        if ($request->has('status') && $request->status) {
            if ($request->status === 'accept') {
                // Show only uploads with accepted items
                $uploadsQuery->whereHas('associates', function($q) {
                    $q->where('status', 'accept');
                });
            } elseif ($request->status === 'pending') {
                // Show only uploads with pending items
                $uploadsQuery->whereHas('associates', function($q) {
                    $q->where('status', 'pending');
                });
            } elseif ($request->status === 'reject') {
                // Show only uploads with rejected items
                $uploadsQuery->whereHas('associates', function($q) {
                    $q->where('status', 'reject');
                });
            }
        }

        $uploads = $uploadsQuery->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get service types mapping for items
        $serviceTypeLabels = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD'
        ];

        // Calculate status counts for tabs
        $statusCounts = [
            'total' => TemporaryUpload::where('user_id', $user->id)->count(),
            'pending' => TemporaryUpload::where('user_id', $user->id)
                ->whereHas('associates', function($q) {
                    $q->where('status', 'pending');
                })->count(),
            'accepted' => TemporaryUpload::where('user_id', $user->id)
                ->whereHas('associates', function($q) {
                    $q->where('status', 'accept');
                })->count(),
            'rejected' => TemporaryUpload::where('user_id', $user->id)
                ->whereHas('associates', function($q) {
                    $q->where('status', 'reject');
                })->count(),
        ];

        return view('customer.services.items', compact('user', 'uploads', 'serviceTypeLabels', 'statusCounts'));
    }

    public function viewUpload($id)
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();

        // Get the specific upload with all its items
        $upload = TemporaryUpload::with(['associates', 'location'])
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Get service types mapping
        $serviceTypeLabels = [
            'register_post' => 'Register Post',
            'slp_courier' => 'SLP Courier',
            'cod' => 'COD'
        ];

        return view('customer.services.view-upload', compact('user', 'upload', 'serviceTypeLabels'));
    }

    public function bulkStatus($id)
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();
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
        $serviceType = $request->input('service_type');

        Log::info('Postal pricing request received', ['weight' => $weight, 'service_type' => $serviceType]);

        // Convert service type to proper format
        $normalizedServiceType = 'register_post'; // Default to register post
        if ($serviceType === 'Register Post') {
            $normalizedServiceType = 'register_post';
        }

        // Use proper pricing calculation
        $price = $this->calculatePostageInternal($normalizedServiceType, $weight, 0);

        Log::info('Calculated postal price', ['weight' => $weight, 'service_type' => $serviceType, 'normalized' => $normalizedServiceType, 'price' => $price]);

        return response()->json([
            'price' => $price,
            'formatted_price' => 'LKR ' . number_format($price, 2)
        ]);
    }

    public function updateBulkItem(Request $request, $id)
    {
        $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_address' => 'required|string',
            'item_value' => 'required|numeric|min:0',
            'service_type' => 'required|in:register_post,slp_courier,cod',
            'weight' => 'required|numeric|min:0',
            'amount' => 'nullable|numeric|min:0',
        ]);

        /** @var User $user */
        $user = Auth::guard('customer')->user();

        $associate = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $associate->update([
            'receiver_name' => $request->receiver_name,
            'receiver_address' => $request->receiver_address,
            'item_value' => $request->item_value,
            'service_type' => $request->service_type,
            'weight' => $request->weight,
            'amount' => $request->amount ?? 0,
        ]);

        return response()->json(['success' => true, 'message' => 'Item updated successfully']);
    }

    public function deleteBulkItem($id)
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();

        $associate = TemporaryUploadAssociate::whereHas('temporaryUpload', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $associate->delete();

        return response()->json(['success' => true, 'message' => 'Item deleted successfully']);
    }

    public function submitBulkToPM($id)
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();

        $temporaryUpload = TemporaryUpload::where('user_id', $user->id)->findOrFail($id);

        // Note: Items are now submitted and will be visible to PM
        // Status tracking will be handled by the existing workflow

        return redirect()->route('customer.services.items')
            ->with('success', 'Items submitted to PM for review successfully!');
    }

    private function calculatePostageInternal($serviceType, $weight, $amount)
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

            default:
                return $weight * 0.1; // Default fallback
        }
    }

    /**
     * Delete bulk upload and all associated records
     */
    public function deleteBulkUpload($id)
    {
        /** @var User $user */
        $user = Auth::guard('customer')->user();

        // Find the temporary upload and ensure it belongs to the current user
        $temporaryUpload = TemporaryUpload::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$temporaryUpload) {
            return redirect()->route('customer.services.items')
                ->with('error', 'Bulk upload not found or access denied.');
        }

        // Check if upload is still pending (only allow deletion of pending uploads)
        if ($temporaryUpload->status !== 'pending') {
            return redirect()->route('customer.services.bulk-status', $id)
                ->with('error', 'Cannot delete processed uploads. Only pending uploads can be deleted.');
        }

        try {
            // Delete all associated temporary upload associate records
            TemporaryUploadAssociate::where('temporary_id', $temporaryUpload->id)->delete();

            // Delete the temporary upload record
            $temporaryUpload->delete();

            return redirect()->route('customer.services.items')
                ->with('success', 'Bulk upload and all associated items have been deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->route('customer.services.bulk-status', $id)
                ->with('error', 'Error deleting bulk upload: ' . $e->getMessage());
        }
    }

    /**
     * Calculate postage for given parameters
     */
    public function calculatePostage(Request $request)
    {
        $request->validate([
            'service_type' => 'required|string',
            'weight' => 'nullable|numeric|min:0',
            'cod_amount' => 'nullable|numeric|min:0'
        ]);

        $serviceType = $request->input('service_type');
        $weight = $request->input('weight', 0);
        $codAmount = $request->input('cod_amount', 0);

        Log::info('Postage calculation request', [
            'service_type' => $serviceType,
            'weight' => $weight,
            'cod_amount' => $codAmount
        ]);

        try {
            $price = null;

            switch ($serviceType) {
                case 'register_post':
                    // Use existing PostPricing model for register post
                    $price = \App\Models\PostPricing::calculatePrice($weight, \App\Models\PostPricing::TYPE_REGISTER);
                    Log::info('Register post price calculated', ['weight' => $weight, 'price' => $price]);
                    break;

                case 'slp_courier':
                    // Use existing SlpPricing model for SLP Courier
                    $price = \App\Models\SlpPricing::calculatePrice($weight);
                    Log::info('SLP courier price calculated', ['weight' => $weight, 'price' => $price]);
                    break;

                case 'cod':
                    // COD = Register post base price + COD charges (2% of amount or min 50 LKR)
                    $basePrice = \App\Models\PostPricing::calculatePrice($weight, \App\Models\PostPricing::TYPE_REGISTER);
                    if ($basePrice && $codAmount > 0) {
                        $codFee = max(50, $codAmount * 0.02); // 2% or min 50 LKR
                        $price = $basePrice + $codFee;
                    } else {
                        $price = $basePrice;
                    }
                    Log::info('COD price calculated', ['base_price' => $basePrice, 'cod_fee' => $codFee ?? 0, 'total' => $price]);
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid service type'
                    ], 400);
            }

            if ($price === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to calculate price for the given parameters'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'price' => number_format($price, 2),
                'price_raw' => $price,
                'service_type' => $serviceType,
                'parameters' => [
                    'weight' => $weight,
                    'cod_amount' => $codAmount
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating price: ' . $e->getMessage()
            ], 500);
        }
    }
}
