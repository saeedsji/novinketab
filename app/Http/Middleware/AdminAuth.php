<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (!Auth::user()->isAdmin()) {
            Log::info('admin access denied ip: ' . $request->ip());
            abort(403, 'شما به این بخش دسترسی ندارید! آی پی شما لاگ شد');
        }
        return $next($request);
    }
}
