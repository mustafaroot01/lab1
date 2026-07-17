<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePatientIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            return response()->json([
                'status'  => false,
                'message' => 'تم إيقاف حسابك من قبل الإدارة، لا يمكنك إجراء هذه العملية.',
            ], 403);
        }

        return $next($request);
    }
}
