<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->unique()->isbn13(),
            'published_date' => $this->faker->date(),
            'description' => $this->faker->paragraph(),
            'image_url' => 'https://placehold.co/200x300',
            'user_id' => User::factory(),

        ];
    }
}
