<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class AdminController extends Controller
{
    protected function failure(
        Throwable $e,
        string $message
    ): RedirectResponse {
        report($e);

        Log::error('Admin action failed.', [
            'exception' => $e,
            'user_id' => auth()->id(),
            'route' => request()->route()?->getName(),
        ]);

        return back()
            ->withInput()
            ->with('error', $message);
    }
}
