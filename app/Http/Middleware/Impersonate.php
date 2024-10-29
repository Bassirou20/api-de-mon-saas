<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Impersonate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('impersonate_user_id')) {
            $user = Auth::user();
            if ($user && $user->canImpersonate()) {
                $impersonateUserId = $request->input('impersonate_user_id');
                $impersonatedUser = \App\Models\User::find($impersonateUserId);
                if ($impersonatedUser) {
                    Auth::login($impersonatedUser);
                }
            }
        }

        return $next($request);
    }
}
