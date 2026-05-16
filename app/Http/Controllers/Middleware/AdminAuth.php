<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle($request, Closure $next)
    {
        // Use the 'admin' guard
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        return redirect()->route('admin_panel.admin.login')->with('error', 'Access denied');
    }
}
