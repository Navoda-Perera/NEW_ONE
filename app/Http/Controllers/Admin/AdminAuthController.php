<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
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

            if (!$user->isInternal()) {
                Auth::logout();
                return back()->withErrors(['nic' => 'Invalid admin credentials.']);
            }

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['nic' => 'Your account has been deactivated.']);
            }

            $request->session()->regenerate();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('pm.dashboard'));
            }
        }

        return back()->withErrors(['nic' => 'Invalid credentials.']);
    }

    public function showRegistrationForm()
    {
        return view('admin.auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nic' => 'required|string|max:20|unique:users',
            'email' => 'nullable|string|email|max:255',
            'mobile' => 'required|string|max:15',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:admin', // Only admin can self-register
        ]);

        $user = User::create([
            'name' => $request->name,
            'nic' => $request->nic,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'password' => Hash::make($request->password),
            'user_type' => 'internal',
            'role' => $request->role,
            'is_active' => true,
        ]);

        Auth::login($user);

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('pm.dashboard');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
