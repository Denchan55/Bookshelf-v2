<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Favorite;    

class FavoritePolicy
{
    /**
     * Create a new policy instance.
     */
    public function delete(User $user, Favorite $favorite)
{
    return $user->id === $favorite->user_id;
}

}
