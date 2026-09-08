<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized admin access.'], 403);
            }

            return redirect()->route('admin.login')->with('error', 'Administrator credentials required.');
        }

        return $next($request);
    }
}
