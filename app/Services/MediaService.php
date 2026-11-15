<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class MediaService
{
    public function storeUploaded(
        UploadedFile $file,
        string $directory
    ): string {
        abort_unless(
            $file->isValid(),
            422,
            'فایل تصویر معتبر نیست.'
        );

        $directory = trim($directory, '/');

        abort_if(
            $directory === '',
            422,
            'مسیر ذخیره‌سازی تصویر معتبر نیست.'
        );

        $path = $file->store($directory, 'public');

        abort_if(
            ! $path,
            500,
            'ذخیره تصویر انجام نشد.'
        );

        return $path;
    }

    public function attach(
        Model $model,
        string $collection,
        UploadedFile $file,
        string $directory,
        ?string $altText = null,
        int $sortOrder = 0
    ): Media {
        $path = $this->storeUploaded($file, $directory);

        [$width, $height] = $this->dimensions($file);

        try {
            return $model->media()->create([
                'collection' => $collection,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
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

    private function dimensions(UploadedFile $file): array
    {
        $size = @getimagesize($file->getRealPath());

        return [
            $size[0] ?? null,
            $size[1] ?? null,
        ];
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
