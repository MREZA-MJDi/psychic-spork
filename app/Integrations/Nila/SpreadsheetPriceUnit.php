<?php

namespace App\Integrations\Nila;

enum SpreadsheetPriceUnit: string
{
    case Rial = 'rial';
    case Toman = 'toman';

    public static function forBatch(array $metadata): self
    {
        return self::from($metadata['price_unit'] ?? self::Toman->value);
    }

    public function toToman(float $amount): float
    {
        return round($this === self::Rial ? $amount / 10 : $amount, 2);
    }
}
