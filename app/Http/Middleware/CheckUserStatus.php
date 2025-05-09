<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && !$request->user()->is_active) {
            // For API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is inactive. Please contact an administrator.'
                ], 403);
            }

            // For web requests (if using web routes)
            // auth()->logout();
            // return redirect()->route('login')
            //     ->with('error', 'Your account is inactive. Please contact an administrator.');
        }

        return $next($request);
    }
}