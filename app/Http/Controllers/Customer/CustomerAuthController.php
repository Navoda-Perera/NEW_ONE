<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustomerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nic' => 'required|string',
            'password' => 'required',
        ]);

        // Find user by NIC and role
        $user = User::where('nic', $request->nic)
                    ->where('role', 'customer')
                    ->where('is_active', true)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'nic' => 'The provided credentials are incorrect.',
            ]);
        }

        // Check if user is external type
        if ($user->user_type !== 'external') {
            return back()->withErrors([
                'nic' => 'Access denied. Customer users must be external users.',
            ]);
        }

        Auth::guard('customer')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('customer.dashboard'));
    }

    public function showRegistrationForm()
    {
        $locations = Location::orderBy('name')->get();
        return view('customer.auth.register', compact('locations'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users',
            'email' => 'nullable|string|email|max:255',
            'mobile' => 'required|string|max:15',
            'company_name' => 'required|string|max:255',
            'company_br' => 'required|string|max:50',
            'location_id' => 'required|exists:locations,id',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'nic' => $request->nic,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'company_name' => $request->company_name,
            'company_br' => $request->company_br,
            'location_id' => $request->location_id,
            'password' => Hash::make($request->password),
            'user_type' => 'external',
            'role' => 'customer',
            'is_active' => true,
        ]);

        Auth::guard('customer')->login($user);
        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
