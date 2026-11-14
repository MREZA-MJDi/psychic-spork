<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use JsonException;

final class ValidProductAttributes implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return;
        }

        if (! is_string($value)) {
            $fail('ساختار ویژگی‌های محصول معتبر نیست.');

            return;
        }

        try {
            $attributes = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $fail('ویژگی‌های محصول باید JSON معتبر باشند.');

            return;
        }

        if (! is_array($attributes) || count($attributes) > 50) {
            $fail('حداکثر ۵۰ ویژگی برای هر محصول قابل ثبت است.');

            return;
        }

        foreach ($attributes as $key => $item) {
            if (! is_string($key) || trim($key) === '' || mb_strlen($key) > 80) {
                $fail('نام هر ویژگی باید متنی و حداکثر ۸۰ نویسه باشد.');

                return;
            }

            if (is_array($item)) {
                if (count($item) > 20) {
                    $fail('برای هر ویژگی حداکثر ۲۰ مقدار قابل ثبت است.');

                    return;
                }

                foreach ($item as $nested) {
                    if (
                        (! is_scalar($nested) && $nested !== null)
                        || (is_string($nested) && mb_strlen($nested) > 500)
                    ) {
                        $fail('مقدار ویژگی‌های محصول معتبر نیست.');

                        return;
                    }
                }

                continue;
            }

            if (
                (! is_scalar($item) && $item !== null)
                || (is_string($item) && mb_strlen($item) > 500)
            ) {
                $fail('مقدار ویژگی‌های محصول معتبر نیست.');

                return;
            }
        }
    }
}
