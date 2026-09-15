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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->is_admin || $user->hasRole('admin') || $user->hasRole('super-admin'))) {
            return $next($request);
        }

        Auth::logout();
        return redirect()->route('admin.login')->withErrors([
            'email' => 'Tài khoản của bạn không có quyền truy cập vào trang Quản trị.'
        ]);
    }
}
