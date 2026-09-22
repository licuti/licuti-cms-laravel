<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Core\Enums\UserStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Bảo vệ: Kiểm tra trạng thái tài khoản trước khi cho vào
            if ($user->status === UserStatus::BANNED) {
                Auth::logout();
                return back()->withErrors(['email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ hỗ trợ.']);
            }

            if ($user->status === UserStatus::INACTIVE) {
                Auth::logout();
                return back()->withErrors(['email' => 'Tài khoản chưa được kích hoạt.']);
            }

            // Bảo vệ: Chỉ cho phép tài khoản có quyền truy cập trang Quản trị
            if ($user->canAccessAdmin()) {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Chào mừng quay trở lại, ' . $user->name . '!');
            }

            Auth::logout();
            return back()->withErrors([
                'email' => 'Tài khoản của bạn không có quyền truy cập vào trang Quản trị.',
            ]);
        }

        return back()->withErrors([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Đăng xuất thành công!');
    }
}
