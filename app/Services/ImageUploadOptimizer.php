<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;

final class ImageUploadOptimizer
{
    public const MAX_UPLOAD_KIB = 10240;
    private const MAX_INPUT_PIXELS = 32_000_000;
    private const MAX_IMAGE_BYTES = 512 * 1024;
    private const MAX_EDGE = 1600;

    /**
     * Return a temporary WebP file when the upload needs resizing or compression.
     * The caller closes the returned stream after storing it.
     *
     * @return resource|null
     */
    public function optimize(UploadedFile $file, string $mime, int $width, int $height)
    {
        abort_if(
            $width * $height > self::MAX_INPUT_PIXELS,
            422,
            'وضوح تصویر بیش از حد بالاست. لطفاً نسخه‌ای تا ۳۲ مگاپیکسل انتخاب کنید.'
        );

        if (max($width, $height) <= self::MAX_EDGE && $file->getSize() <= self::MAX_IMAGE_BYTES) {
            return null;
        }

        abort_unless(
            function_exists('imagewebp') && (imagetypes() & IMG_WEBP),
            503,
            'فشرده‌سازی تصویر در دسترس نیست. لطفاً با پشتیبانی تماس بگیرید.'
        );

        $path = $file->getPathname();
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($path) : false,
            default => false,
        };

        abort_unless($image instanceof GdImage, 422, 'خواندن این تصویر ممکن نیست. لطفاً نسخهٔ JPEG، PNG یا WebP ثابت را انتخاب کنید.');

        $orientation = $mime === 'image/jpeg' && function_exists('exif_read_data')
            ? (int) (@exif_read_data($path)['Orientation'] ?? 1)
            : 1;

        $temporary = tmpfile();
        abort_if($temporary === false, 500, 'آماده‌سازی تصویر انجام نشد.');

        try {
            foreach ([[self::MAX_EDGE, 82], [self::MAX_EDGE, 70], [1200, 72], [850, 68]] as [$edge, $quality]) {
                $scale = min(1, $edge / max(imagesx($image), imagesy($image)));
                $output = $this->resize(
                    $image,
                    max(1, (int) round(imagesx($image) * $scale)),
                    max(1, (int) round(imagesy($image) * $scale))
                );

                $output = $this->orient($output, $orientation);

                ftruncate($temporary, 0);
                rewind($temporary);
                $written = imagewebp($output, $temporary, $quality);
                unset($output);
                $bytes = ftell($temporary);

                if ($written && $bytes > 0 && $bytes <= self::MAX_IMAGE_BYTES) {
                    rewind($temporary);

                    return $temporary;
                }
            }

            abort(422, 'حجم تصویر پس از فشرده‌سازی همچنان زیاد است. لطفاً عکس دیگری انتخاب کنید.');
        } catch (\Throwable $exception) {
            fclose($temporary);

            throw $exception;
        }
    }

    private function resize(GdImage $image, int $width, int $height): GdImage
    {
        $output = imagecreatetruecolor($width, $height);
        imagealphablending($output, false);
        imagesavealpha($output, true);
        imagecopyresampled($output, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $output;
    }

    private function orient(GdImage $image, int $orientation): GdImage
    {
        if (in_array($orientation, [2, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        } elseif ($orientation === 4) {
            imageflip($image, IMG_FLIP_VERTICAL);
        }

        $rotation = match ($orientation) {
            3 => 180,
            5, 8 => 90,
            6, 7 => -90,
            default => 0,
        };

        return $rotation !== 0 ? imagerotate($image, $rotation, 0) : $image;
    }
}
