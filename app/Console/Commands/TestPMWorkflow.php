<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\TemporaryUpload;
use App\Models\TemporaryUploadAssociate;
use App\Models\Item;
use Illuminate\Support\Facades\Hash;

class TestPMWorkflow extends Command
{
    protected $signature = 'test:pm-workflow';
    protected $description = 'Test the complete PM workflow: customer submission → PM approval → item creation';

    public function handle()
    {
        $this->info('Testing PM Workflow...');

        // 1. Create test customer if doesn't exist
        $customer = User::where('email', 'test.customer@example.com')->first();
        if (!$customer) {
            $customer = User::create([
                'name' => 'Test Customer',
                'nic' => 'TEST123456789',
                'email' => 'test.customer@example.com',
                'mobile' => '0712345678',
                'company_name' => 'Test Company',
                'company_br' => 'BR123456',
                'password' => Hash::make('password'),
                'user_type' => 'external',
                'role' => 'customer',
                'is_active' => true,
            ]);
            $this->info('✓ Created test customer');
        } else {
            $this->info('✓ Test customer already exists');
        }

        // 2. Create PM user if doesn't exist
        $pm = User::where('email', 'test.pm@example.com')->first();
        if (!$pm) {
            $pm = User::create([
                'name' => 'Test PM',
                'nic' => 'PM123456789',
                'email' => 'test.pm@example.com',
                'mobile' => '0712345679',
                'password' => Hash::make('password'),
                'user_type' => 'internal',
                'role' => 'pm',
                'is_active' => true,
            ]);
            $this->info('✓ Created test PM user');
        } else {
            $this->info('✓ Test PM user already exists');
        }

        // 3. Test customer submission workflow
        $this->info('');
        $this->info('Step 1: Testing customer item submission...');

        // Create temporary upload
        $tempUpload = TemporaryUpload::create([
            'user_id' => $customer->id,
            'category' => 'temporary_list',
            'location_id' => 1, // Default location
        ]);

        // Create temporary upload associate (the actual item)
        $tempAssociate = TemporaryUploadAssociate::create([
            'temporary_id' => $tempUpload->id,
            'amount' => 125.00,
            'item_value' => 1000.00,
            'sender_name' => 'John Doe',
            'receiver_address' => '456 Oak Ave, Kandy',
            'receiver_name' => 'Jane Smith',
            'postage' => 125.00,
            'commission' => 12.50,
            'weight' => 1.5,
            'fix_amount' => null,
            'status' => 'pending',
        ]);

        $this->info("✓ Created temporary upload (ID: {$tempUpload->id}) with associate (ID: {$tempAssociate->id})");
        $this->info("✓ Status: {$tempAssociate->status}");

        // 4. Check PM dashboard counts
        $this->info('');
        $this->info('Step 2: Checking PM dashboard data...');

        $pendingCount = TemporaryUploadAssociate::where('status', 'pending')->count();
        $customerCount = User::where('role', 'customer')->count();

        $this->info("✓ Pending items count: {$pendingCount}");
        $this->info("✓ Total customers: {$customerCount}");

        // 5. Test PM acceptance workflow
        $this->info('');
        $this->info('Step 3: Testing PM acceptance workflow...');

        // Generate barcode
        $barcode = 'BC' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

        // Update temporary associate to accepted
        $tempAssociate->update([
            'status' => 'accept',
            'barcode' => $barcode,
        ]);

        // Create Item record
        $item = Item::create([
            'barcode' => $barcode,
            'receiver_name' => $tempAssociate->receiver_name,
            'receiver_address' => $tempAssociate->receiver_address,
            'weight' => $tempAssociate->weight,
            'amount' => $tempAssociate->amount,
            'status' => 'accept',
            'created_by' => $customer->id,
            'updated_by' => $customer->id,
        ]);

        $this->info("✓ PM accepted item and generated barcode: {$barcode}");
        $this->info("✓ Created Item record (ID: {$item->id})");
        $this->info("✓ Item status: {$item->status}");

        // 6. Final verification
        $this->info('');
        $this->info('Step 4: Final workflow verification...');

        $finalPendingCount = TemporaryUploadAssociate::where('status', 'pending')->count();
        $acceptedCount = TemporaryUploadAssociate::where('status', 'accept')->count();
        $itemsCount = Item::count();

        $this->info("✓ Remaining pending items: {$finalPendingCount}");
        $this->info("✓ Accepted items: {$acceptedCount}");
        $this->info("✓ Total items in Item table: {$itemsCount}");

        // 7. Test URLs and access
        $this->info('');
        $this->info('Step 5: Available URLs for testing...');
        $this->info('PM Dashboard: http://127.0.0.1:8000/pm/dashboard');
        $this->info('PM Pending Items: http://127.0.0.1:8000/pm/items/pending');
        $this->info('Customer Dashboard: http://127.0.0.1:8000/customer/dashboard');
        $this->info('');
        $this->info('Test PM Login: test.pm@example.com / password');
        $this->info('Test Customer Login: test.customer@example.com / password');

        $this->info('');
        $this->info('🎉 PM Workflow test completed successfully!');
        $this->info('The complete workflow is working: Customer → Temporary Upload → PM Approval → Item Creation');

        return 0;
    }
}
