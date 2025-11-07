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
        $internalUsers = User::where('user_type', 'internal')->count();
        $externalUsers = User::where('user_type', 'external')->count();

        return view('admin.dashboard', compact('totalUsers', 'adminUsers', 'pmUsers', 'postmanUsers', 'customerUsers', 'internalUsers', 'externalUsers'));
    }

    public function users()
    {
        $users = User::with('location')->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
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

        // Add location_id only for PM role
        if ($request->role === 'pm') {
            $userData['location_id'] = $request->location_id;
        }

        User::create($userData);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully!");
    }
}
