<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $reviews = Review::all();

        foreach ($users as $user) {
            $reviewIds = $reviews->random(random_int(5, 10))->pluck('id')->unique();

            foreach ($reviewIds as $reviewId) {
                Like::create([
                    'user_id' => $user->id,
                    'review_id' => $reviewId,
                ]);
            }
        }
    }
}
