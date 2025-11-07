<?php

namespace App\Http\Controllers\PM;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            return back()->withErrors([
                'nic' => 'The provided credentials are incorrect.',
            ])->onlyInput('nic');
        }

        // Check if user is internal type
        if ($user->user_type !== 'internal') {
            return back()->withErrors([
                'nic' => 'Access denied. PM users must be internal users.',
            ])->onlyInput('nic');
        }

        // Use PM guard for authentication
        Auth::guard('pm')->login($user, $request->filled('remember'));

        // Regenerate session ID for security
        $request->session()->regenerate();

        // Log successful login for debugging
        Log::info('PM user logged in successfully', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'session_id' => $request->session()->getId(),
            'guard' => 'pm'
        ]);

        return redirect()->intended(route('pm.dashboard'))
                       ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Handle PM logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('pm')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/pm/login');
    }
}
