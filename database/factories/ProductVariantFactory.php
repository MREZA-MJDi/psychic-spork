<?php

namespace DatabaseFactories;

use AppModelsProductVariant;
use IlluminateDatabaseEloquentFactoriesFactory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => null,
            'sku' => 'SKU-' . strtoupper(fake()->unique()->bothify('????-#####')),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'color' => fake()->randomElement(['مشکی', 'سفید', 'کرم', 'نود']),
            'color_code' => fake()->randomElement(['#211e20', '#f7f7f2', '#e9d6c5', '#d9b6a7']),
            'price' => fake()->numberBetween(300000, 5000000),
            'sale_price' => null,
            'wholesale_price' => null,
            'stock' => fake()->numberBetween(0, 50),
            'low_stock_threshold' => 5,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
