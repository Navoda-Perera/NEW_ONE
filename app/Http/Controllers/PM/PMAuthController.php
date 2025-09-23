<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class PMAuthController extends Controller
{
    /**
     * Show the PM login form.
     */
    public function showLoginForm()
    {
        return view('pm.auth.login');
    }

    /**
     * Handle PM login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'nic' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by NIC and role
        $user = User::where('nic', $request->nic)
                    ->where('role', 'pm')
                    ->where('is_active', true)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'nic' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is internal type
        if ($user->user_type !== 'internal') {
            throw ValidationException::withMessages([
                'nic' => ['Access denied. PM users must be internal users.'],
            ]);
        }

        Auth::login($user);

        return redirect()->intended(route('pm.dashboard'));
    }

    /**
     * Handle PM logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pm.login');
    }
}
