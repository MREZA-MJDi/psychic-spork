<?php

namespace Tests\Unit;

use App\Rules\ValidProductAttributes;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ValidProductAttributesTest extends TestCase
{
    public function test_valid_product_attributes_are_accepted(): void
    {
        $validator = Validator::make(
            ['attributes_json' => '{"جنس":"نخ","ویژگی‌ها":["نرم","سبک"]}'],
            ['attributes_json' => [new ValidProductAttributes()]]
        );

        $this->assertTrue($validator->passes());
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_product_attribute_payloads_are_rejected(string $payload): void
    {
        $validator = Validator::make(
            ['attributes_json' => $payload],
            ['attributes_json' => [new ValidProductAttributes()]]
        );

        $this->assertTrue($validator->fails());
    }

    public static function invalidPayloads(): array
    {
        return [
            'malformed JSON' => ['{"fabric":'],
            'top-level list' => ['["one","two"]'],
            'nested object' => ['{"details":{"inner":"value"}}'],
            'numeric key' => ['{"0":"invalid"}'],
        ];
    }
}
