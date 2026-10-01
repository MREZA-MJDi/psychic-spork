<?php

namespace DatabaseFactories;

use AppModelsBrand;
use IlluminateDatabaseEloquentFactoriesFactory;
use IlluminateSupportStr;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->optional()->sentence(),
            'meta_title' => null,
            'meta_description' => null,
            'is_active' => true,
        ];
    }
}
