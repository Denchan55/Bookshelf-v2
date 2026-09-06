<?php

namespace Database\Factories;

use App\Models\Favorite;
use App\Models\User;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
        ];
    }
}
