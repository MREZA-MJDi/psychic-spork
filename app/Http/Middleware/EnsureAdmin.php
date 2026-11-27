<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        abort_unless(
            $user->isAdmin(),
            403,
            'این بخش فقط برای مدیر فروشگاه است.'
        );

        $response = $next($request);
        // Authenticated admin HTML and filter results must never be shared by a CDN.
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }
}
