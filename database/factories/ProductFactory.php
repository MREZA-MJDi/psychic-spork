<?php

namespace DatabaseFactories;

use AppModelsProduct;
use IlluminateDatabaseEloquentFactoriesFactory;
use IlluminateSupportStr;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => null,
            'brand_id' => null,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 999999),
            'short_description' => fake()->optional()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'attributes' => null,
            'meta_title' => null,
            'meta_description' => null,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }
}
