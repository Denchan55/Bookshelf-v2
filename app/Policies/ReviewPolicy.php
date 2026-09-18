<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function update(User $user, Review $review)
    {
        return $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review)
    {
        return $review->user_id === $user->id;
    }

    public function edit(User $user, Review $review)
    {
        return $user->id === $review->user_id;
    }
}
