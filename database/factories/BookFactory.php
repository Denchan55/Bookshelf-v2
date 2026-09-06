<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'author' => $this->faker->name(),
            'isbn' => $this->faker->unique()->isbn13(),
            'published_at' => $this->faker->date(),
            'description' => $this->faker->paragraph(),
            'image_url' => 'https://placehold.co/200x300', // ★正しい値
            'user_id' => User::factory(), // ★OK
            // ★ genre_id は削除（複数ジャンル方式では不要）
        ];
    }
}
