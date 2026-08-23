<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();

        $bookIds = Book::inRandomOrder()->take(3)->pluck('id');

        foreach ($bookIds as $bookId) {
            $user->favoriteBooks()->syncWithoutDetaching([$bookId]);
        }
    }
}
