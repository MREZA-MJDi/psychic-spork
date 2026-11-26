<?php

namespace App\Support;

final class PersianNumber
{
    public static function digits(string|int|float|null $value): string
    {
        return strtr((string) $value, array_combine(
            range(0, 9),
            preg_split('//u', '۰۱۲۳۴۵۶۷۸۹', -1, PREG_SPLIT_NO_EMPTY)
        ));
    }

    public static function format(float $value, int $decimals = 0): string
    {
        return self::digits(number_format($value, $decimals, '٫', '٬'));
    }
}
