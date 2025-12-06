<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Not logged in
        if (!$user) {
            return redirect()->route('login')->with('error', 'You must be logged in.');
        }

        // Role not allowed
        if (!in_array(strtolower($user->role), array_map('strtolower', $roles))) {
            // Redirect based on role
            switch ($user->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard')
                        ->with('error', 'Access denied to dispatcher area.');
                case 'dispatcher':
                    return redirect()->route('dispatcher.dashboard')
                        ->with('error', 'Access denied to admin area.');
                default:
                    return redirect()->route('login')
                        ->with('error', 'Unauthorized access.');
            }
        }

        return $next($request);
    }
}
