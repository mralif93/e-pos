<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Stores the selected outlet_id in the session
 * so it persists across page navigations.
 *
 * Priority:
 *  1. 'outlet_id' explicitly set in request (including "" to clear)
 *  2. Session-stored value
 *  3. Authenticated user's own outlet (for non-Super Admin)
 */
class SetActiveOutlet
{
    public function handle(Request $request, Closure $next)
    {
        // Only apply to authenticated admin web routes
        if (!$request->is('admin/*') && !$request->is('admin')) {
            return $next($request);
        }

        // If outlet_id is explicitly in this request, update the session
        if ($request->has('outlet_id')) {
            $value = $request->input('outlet_id');
            if ($value === '' || $value === null) {
                // Explicitly cleared → remove from session
                session()->forget('active_outlet_id');
            } else {
                session(['active_outlet_id' => $value]);
            }
        }

        // Merge session outlet_id into the request if not already present
        if (!$request->has('outlet_id') && session()->has('active_outlet_id')) {
            $request->merge(['outlet_id' => session('active_outlet_id')]);
        }

        return $next($request);
    }
}
