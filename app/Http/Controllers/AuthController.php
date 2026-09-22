<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        return Auth::check() ? $this->redirectAfterLogin() : view('auth.login');
    }

    public function login(LoginRequest $request, CartService $cart): RedirectResponse
    {
        try {
            $data = $request->validated();

            $identifier = $data['identifier'];
            $user = User::query()
                ->where(function ($query) use ($identifier): void {
                    $query
                        ->where('email', $identifier)
                        ->orWhere(function ($query) use ($identifier): void {
                            $query
                                ->where('name', $identifier)
                                ->where('is_admin', true);
                        });
                })
                ->first();

            if (!$user || ! Auth::attempt([
                'email' => $user->email,
                'password' => $data['password'],
            ], $request->boolean('remember'))) {
                return back()
                    ->withInput($request->only('identifier'))
                    ->withErrors(['identifier' => 'ایمیل/نام کاربری یا رمز عبور نادرست است.'])
                    ->with('error', 'ورود انجام نشد؛ اطلاعات حساب را بررسی کنید.');
            }

            // Merge the guest cart while the original session id is still available.
            $cart->mergeGuestIntoUser($request, $request->user());

            $request->session()->regenerate();

            return $this->redirectAfterLogin()->with('success', 'با موفقیت وارد شدید.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('identifier'))
                ->with('error', 'ورود انجام نشد. دوباره تلاش کنید.');
        }
    }

    public function showRegister(): View|RedirectResponse
    {
        return Auth::check() ? $this->redirectAfterLogin() : view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            $data = $request->validated();

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_admin' => false,
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()
                ->route('account')
                ->with('success', 'حساب شما با موفقیت ساخته شد.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'ساخت حساب انجام نشد. دوباره تلاش کنید.');
        }
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'از حساب خارج شدید.');
    }

    private function redirectAfterLogin(): RedirectResponse
    {
        return Auth::user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('account');
    }
}
