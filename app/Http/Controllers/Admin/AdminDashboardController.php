<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $adminUsers = User::where('role', 'admin')->count();
        $pmUsers = User::where('role', 'pm')->count();
        $postmanUsers = User::where('role', 'postman')->count();
        $customerUsers = User::where('role', 'customer')->count();

        return view('admin.modern-dashboard', compact('totalUsers', 'adminUsers', 'pmUsers', 'postmanUsers', 'customerUsers'));
    }

    public function users(Request $request)
    {
        $query = User::with('location');

        // Search by NIC
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nic', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by user type
        if ($request->filled('type')) {
            $query->where('user_type', $request->type);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        // Append query parameters to pagination links
        $users->appends($request->query());

        return view('admin.users.modern-index', compact('users'));
    }

    public function createUser()
    {
        $locations = Location::active()->orderBy('name')->get();
        return view('admin.users.create', compact('locations'));
    }

    public function storeUser(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users',
            'email' => 'nullable|string|email|max:255',
            'mobile' => 'required|string|regex:/^[0-9]{10}$/',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,pm,postman',
        ];

        // Add location validation for PM and Postman roles
        if (in_array($request->role, ['pm', 'postman'])) {
            $validationRules['location_id'] = 'required|exists:locations,id';
        }

        $request->validate($validationRules);

        $userData = [
            'name' => $request->name,
            'nic' => $request->nic,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => bcrypt($request->password),
            'user_type' => 'internal',
            'role' => $request->role,
            'is_active' => true,
        ];

        // Add location_id for PM and Postman roles
        if (in_array($request->role, ['pm', 'postman'])) {
            $userData['location_id'] = $request->location_id;
        }

        User::create($userData);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function editUser(User $user)
    {
        $locations = Location::active()->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'locations'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users,nic,' . $user->id,
            'email' => 'nullable|string|email|max:255',
            'mobile' => 'required|string|regex:/^[0-9]{10}$/',
            'user_type' => 'required|in:internal,external',
            'role' => 'required|in:admin,pm,postman,customer',
            'is_active' => 'boolean',
        ];

        // Add location validation for PM and Postman roles
        if (in_array($request->role, ['pm', 'postman'])) {
            $validationRules['location_id'] = 'required|exists:locations,id';
        }

        // Add password validation only if password is provided
        if ($request->filled('password')) {
            $validationRules['password'] = 'string|min:8|confirmed';
        }

        $request->validate($validationRules);

        $userData = [
            'name' => $request->name,
            'nic' => $request->nic,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'user_type' => $request->user_type,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Update password only if provided
        if ($request->filled('password')) {
            $userData['password'] = bcrypt($request->password);
        }

        // Handle location_id based on role
        if (in_array($request->role, ['pm', 'postman'])) {
            $userData['location_id'] = $request->location_id;
        } else {
            $userData['location_id'] = null;
        }

        $user->update($userData);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully!");
    }
}
