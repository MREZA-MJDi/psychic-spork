<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class MediaService
{
    public function __construct(private readonly ImageUploadOptimizer $optimizer)
    {
    }

    private function storeUpload(UploadedFile $file, string $directory): array
    {
        abort_unless($file->isValid(), 422, 'فایل تصویر معتبر نیست.');

        $directory = trim($directory, '/');
        abort_if($directory === '', 422, 'مسیر ذخیره‌سازی تصویر معتبر نیست.');

        $disk = Storage::disk('public');
        $reserve = (int) config('filesystems.media_min_free_mib') * 1024 * 1024;
        if ($reserve > 0 && config('filesystems.disks.public.driver') === 'local') {
            $free = @disk_free_space($disk->path(''));
            abort_if(
                $free !== false && $free < $reserve + $file->getSize(),
                507,
                'فضای کافی برای ذخیرهٔ رسانه وجود ندارد. لطفاً با پشتیبانی تماس بگیرید.'
            );
        }

        $mime = $file->getMimeType();
        $size = $file->getSize();
        $dimensions = @getimagesize($file->getPathname());
        $width = $dimensions[0] ?? null;
        $height = $dimensions[1] ?? null;
        $optimized = $dimensions
            ? $this->optimizer->optimize($file, $mime ?? '', $width, $height)
            : null;

        if ($optimized === null) {
            $path = $file->store($directory, 'public');
            abort_unless($path, 500, 'ذخیره تصویر انجام نشد.');

            return [$path, $mime, $size, $width, $height];
        }

        try {
            $temporaryPath = stream_get_meta_data($optimized)['uri'];
            $storedDimensions = getimagesize($temporaryPath);
            $storedBytes = fstat($optimized)['size'];
            $path = $directory . '/' . Str::uuid() . '.webp';

            abort_unless(
                $storedDimensions && $disk->put($path, $optimized),
                500,
                'ذخیره تصویر انجام نشد.'
            );

            return [
                $path,
                'image/webp',
                $storedBytes,
                $storedDimensions[0],
                $storedDimensions[1],
            ];
        } finally {
            fclose($optimized);
        }
    }

    public function attach(
        Model $model,
        string $collection,
        UploadedFile $file,
        string $directory,
        ?string $altText = null,
        int $sortOrder = 0
    ): Media {
        [$path, $mime, $size, $width, $height] = $this->storeUpload($file, $directory);

        try {
            return $model->media()->create([
                'collection' => $collection,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'alt_text' => $altText,
                'sort_order' => $sortOrder,
                'uploaded_by' => auth()->id(),
            ]);
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);

            throw $e;
        }
    }

    public function replace(
        Model $model,
        string $collection,
        UploadedFile $file,
        string $directory,
        ?string $altText = null
    ): Media {
        $newMedia = null;

        try {
            return DB::transaction(function () use (
                $model,
                $collection,
                $file,
                $directory,
                $altText,
                &$newMedia
            ): Media {
                $old = $model->media()
                    ->where('collection', $collection)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                $newMedia = $this->attach(
                    $model,
                    $collection,
                    $file,
                    $directory,
                    $altText,
                    $old?->sort_order ?? 0
                );

                if ($old) {
                    $this->deleteRecordAfterCommit($old);
                }

                return $newMedia;
            });
        } catch (Throwable $e) {
            if ($newMedia instanceof Media && filled($newMedia->path)) {
                Storage::disk($newMedia->disk ?: 'public')->delete(
                    $this->normalizePath($newMedia->path)
                );
            }

            throw $e;
        }
    }

    public function remove(Media $media): void
    {
        DB::transaction(function () use ($media): void {
            $this->deleteRecordAfterCommit($media);
        });
    }

    public function removeCollection(
        Model $model,
        string $collection
    ): void {
        DB::transaction(function () use ($model, $collection): void {
            $model->media()
                ->where('collection', $collection)
                ->orderBy('id')
                ->chunkById(100, function ($items): void {
                    foreach ($items as $item) {
                        $this->deleteRecordAfterCommit($item);
                    }
                });
        });
    }

    private function deleteRecordAfterCommit(Media $media): void
    {
        $disk = $media->disk ?: 'public';
        $path = $this->normalizePath((string) $media->path);

        $media->delete();

        if ($path !== '') {
            DB::afterCommit(function () use ($disk, $path): void {
                Storage::disk($disk)->delete($path);
            });
        }
    }

    public function delete(?string $path): void
    {
        if (! $path || $this->isExternal($path)) {
            return;
        }

        $path = $this->normalizePath($path);

        if ($path !== '') {
            Storage::disk('public')->delete($path);
        }
    }

    public function deleteMedia(Media $media): void
    {
        if (! $media->path || $this->isExternal($media->path)) {
            return;
        }

        $disk = $media->disk ?: 'public';
        $path = $this->normalizePath($media->path);

        if ($path !== '') {
            Storage::disk($disk)->delete($path);
        }
    }

    public function exists(?string $value): bool
    {
        $value = trim((string) $value);

        if (
            $value === ''
            || $this->isExternal($value)
        ) {
            return false;
        }

        return Storage::disk('public')
            ->exists($this->normalizePath($value));
    }

    private function normalizePath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return ltrim($path, '/');
    }

    private function isExternal(string $value): bool
    {
        return (bool) preg_match(
            '/^https?:\/\//i',
            trim($value)
        );
    }
}
