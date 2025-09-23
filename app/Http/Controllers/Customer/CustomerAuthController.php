<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        $credentials = $request->only('nic', 'password');

        if (Auth::attempt($credentials)) {
            /** @var User $user */
            $user = Auth::user();

            if (!$user->isExternalCustomer()) {
                Auth::logout();
                return back()->withErrors(['nic' => 'Invalid customer credentials.']);
            }

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['nic' => 'Your account has been deactivated.']);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors(['nic' => 'Invalid credentials.']);
    }

    public function showRegistrationForm()
    {
        return view('customer.auth.register');
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
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
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

        Auth::login($user);
        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
