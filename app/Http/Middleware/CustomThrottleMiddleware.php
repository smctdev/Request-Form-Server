<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CustomThrottleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $maxAttempts, $decayMinutes): Response
    {
        $user = User::where('email', $request->email)
            ->orWhere('userName', $request->email)->first();

        $key = "loginAttempts: {$user->id}";

        if (RateLimiter::tooManyAttempts(
            $key,
            $maxAttempts
        )) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'status'  => false,
                'message' => "Too many attempts. You may try again in {$seconds} seconds."
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
