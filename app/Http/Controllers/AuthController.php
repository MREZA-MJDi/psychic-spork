<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        $this->rememberAuthContinuation($request);

        if (Auth::check()) {
            return $this->redirectAfterLogin();
        }

        $demoCredentials = null;
        if (app()->environment(['local', 'testing'])) {
            $phone = (string) config('app.admin.phone');
            $password = (string) config('app.admin.password');
            $admin = $phone !== ''
                ? User::query()->where('phone', $phone)->where('is_admin', true)->first()
                : null;

            if ($admin && $password !== '' && Hash::check($password, $admin->password)) {
                $demoCredentials = [
                    'phone' => $phone,
                    'password' => $password,
                ];
            }
        }

        return view('auth.login', compact('demoCredentials'));
    }

    public function login(LoginRequest $request, CartService $cart): RedirectResponse
    {
        try {
            $data = $request->validated();

            if (! Auth::attempt([
                'phone' => $data['phone'],
                'password' => $data['password'],
            ], $request->boolean('remember'))) {
                return back()
                    ->withInput($request->only('phone'))
                    ->withErrors(['phone' => 'شماره موبایل یا رمز عبور نادرست است.'])
                    ->with('error', 'ورود انجام نشد؛ اطلاعات حساب را بررسی کنید.');
            }

            // Merge the guest cart while the original session id is still available.
            $cart->mergeGuestIntoUser($request, $request->user());

            $request->session()->regenerate();

            return $this->redirectAfterLogin()->with('success', 'با موفقیت وارد شدید.');
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('phone'))
                ->with('error', 'ورود انجام نشد. دوباره تلاش کنید.');
        }
    }

    public function showRegister(Request $request): View|RedirectResponse
    {
        $this->rememberAuthContinuation($request);

        if (Auth::check()) {
            return $this->redirectAfterLogin();
        }

        return view('auth.register');
    }

    public function register(RegisterRequest $request, CartService $cart): RedirectResponse
    {
        try {
            $data = $request->validated();

            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => $data['password'],
                'is_admin' => false,
            ]);

            $cart->mergeGuestIntoUser($request, $user);
            Auth::login($user);
            $request->session()->regenerate();

            return $this->redirectAfterLogin()
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
        $user = Auth::user();

        if ($user->isAdmin()) {
            session()->forget('url.intended');

            return redirect()->route('admin.dashboard');
        }

        // Honor only this explicit, same-app workflow. Never redirect to an
        // arbitrary URL supplied through the intended session value.
        if (session()->pull('auth.continue') === 'cheque') {
            session()->forget('url.intended');

            return redirect()->to(route('wholesale.show') . '#cheque-application');
        }

        $intendedPath = parse_url((string) session()->pull('url.intended'), PHP_URL_PATH);
        $chequeInfoPath = parse_url(route('wholesale.show'), PHP_URL_PATH);

        return $intendedPath === $chequeInfoPath
            ? redirect()->route('wholesale.show')
            : redirect()->route('account');
    }

    private function rememberAuthContinuation(Request $request): void
    {
        if ($request->query('continue') === 'cheque') {
            $request->session()->put('auth.continue', 'cheque');
        }
    }
}
