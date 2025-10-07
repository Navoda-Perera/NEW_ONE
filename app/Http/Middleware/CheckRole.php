<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            // Determine the correct login route based on the request path
            if ($request->is('admin/*')) {
                return redirect()->route('admin.login')->with('info', 'Please login to access admin area.');
            } elseif ($request->is('pm/*')) {
                return redirect()->route('pm.login')->with('info', 'Please login to access PM area.');
            } elseif ($request->is('customer/*')) {
                return redirect()->route('customer.login')->with('info', 'Please login to access customer area.');
            } else {
                // Default to admin login for root paths
                return redirect()->route('admin.login');
            }
        }

        $user = Auth::user();

        // Check if user exists
        if (!$user) {
            Auth::logout();
            if ($request->is('pm/*')) {
                return redirect()->route('pm.login')->with('error', 'Session expired. Please login again.');
            }
            return redirect()->route('admin.login');
        }

        if (!$user->is_active) {
            Auth::logout();
            // Determine the correct login route based on the request path
            if ($request->is('admin/*')) {
                return redirect()->route('admin.login')->with('error', 'Your account has been deactivated.');
            } elseif ($request->is('pm/*')) {
                return redirect()->route('pm.login')->with('error', 'Your account has been deactivated.');
            } elseif ($request->is('customer/*')) {
                return redirect()->route('customer.login')->with('error', 'Your account has been deactivated.');
            } else {
                return redirect()->route('admin.login')->with('error', 'Your account has been deactivated.');
            }
        }

        if (!in_array($user->role, $roles)) {
            // Log unauthorized access attempt
            Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'path' => $request->path()
            ]);

            // Redirect to appropriate dashboard based on user role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with('error', 'Access denied for this section.');
            } elseif ($user->role === 'pm') {
                return redirect()->route('pm.dashboard')->with('error', 'Access denied for this section.');
            } elseif ($user->role === 'customer') {
                return redirect()->route('customer.dashboard')->with('error', 'Access denied for this section.');
            }

            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
