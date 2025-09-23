<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        // Debug: Check authentication status
        Log::info('PM Dashboard - Auth check:', [
            'is_authenticated' => Auth::check(),
            'auth_id' => Auth::id(),
            'auth_user' => Auth::user() ? Auth::user()->name : 'null'
        ]);

        // Get current authenticated user with location
        $currentUser = Auth::user();
        if ($currentUser) {
            $currentUser = User::with('location')->find($currentUser->id);
            Log::info('PM Dashboard - User loaded:', [
                'user_name' => $currentUser->name,
                'user_id' => $currentUser->id,
                'location_id' => $currentUser->location_id,
                'has_location' => $currentUser->location ? 'yes' : 'no'
            ]);
        } else {
            Log::warning('PM Dashboard - No authenticated user found');
        }

        return view('pm.dashboard', compact('customerUsers', 'activeCustomers', 'externalCustomers', 'currentUser'));
    }

    public function customers()
    {
        $customers = User::where('role', 'customer')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);

        // Get current authenticated user with location using eager loading
        $currentUser = User::with('location')->where('id', Auth::id())->first();

        return view('pm.customers.index', compact('customers', 'currentUser'));
    }

    // Create Customer methods
    public function createCustomer()
    {
        // Get current user's location information with fallback
        $currentUser = null;
        if (Auth::check()) {
            $currentUser = User::with('location')->find(Auth::id());
        }
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

        // Get current user's location information with fallback
        $currentUser = null;
        if (Auth::check()) {
            $currentUser = User::with('location')->find(Auth::id());
        }

        return view('pm.postmen.index', compact('postmen', 'currentUser'));
    }

    public function createPostman()
    {
        // Get current user's location information with fallback
        $currentUser = null;
        if (Auth::check()) {
            $currentUser = User::with('location')->find(Auth::id());
        }
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
}
