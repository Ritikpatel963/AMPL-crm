<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (isset($user->status) && (int) $user->status === 0) {
            return response()->json([
                'status' => false,
                'message' => 'This account is inactive.',
            ], 403);
        }

        if ($user->role === 'vendor' && $user->approval_status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'Your vendor account is not approved yet.',
                'approval_status' => $user->approval_status,
            ], 403);
        }

        if (in_array($user->role, ['agent', 'subadmin'], true)) {
            if ($user->crm_status !== null && $user->crm_status !== 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'This CRM account is not active.',
                ], 403);
            }

            if ($user->expires_at !== null && now()->startOfDay()->gt($user->expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'This CRM account has expired.',
                ], 403);
            }
        }

        return $next($request);
    }
}
