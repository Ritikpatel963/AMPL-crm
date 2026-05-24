<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCallingCrmAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (Auth::guard('admin')->check() || $user instanceof Admin) {
            return $next($request);
        }

        if ($user && in_array($user->role, ['agent', 'subadmin'], true)) {
            return $next($request);
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthorized.',
        ], 403);
    }
}
