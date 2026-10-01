<?php

namespace DatabaseFactories;

use AppModelsCategory;
use IlluminateDatabaseEloquentFactoriesFactory;
use IlluminateSupportStr;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->optional()->sentence(),
            'meta_title' => null,
            'meta_description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
