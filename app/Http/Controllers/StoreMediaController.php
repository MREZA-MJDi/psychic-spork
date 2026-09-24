<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StoreMediaController extends Controller
{
    public function show(
        string $path,
        MediaService $media
    ): Response {
        $path = ltrim($path, '/');

        abort_if(
            $path === ''
            || str_contains($path, '..')
            || str_contains($path, '\\'),
            404
        );

        abort_unless(
            $media->exists($path),
            404
        );

        return Storage::disk('public')->response(
            $path,
            null,
            [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]
        );
    }
}
