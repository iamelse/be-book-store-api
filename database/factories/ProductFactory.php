<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->numberBetween(50000, 200000),
            'isbn' => $this->faker->isbn13(),
            'author' => $this->faker->name(),
            'publisher' => $this->faker->company(),
            'cover_image' => $this->faker->imageUrl(200,300,'books'),
            'stock'       => $this->faker->numberBetween(5, 50),
        ];
    }
}
