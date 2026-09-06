<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Book;
use App\Models\Review;
use App\Models\Favorite;
use App\Policies\BookPolicy;
use App\Policies\ReviewPolicy;
use App\Policies\FavoritePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
    Book::class => BookPolicy::class,
    Review::class => ReviewPolicy::class,
    Favorite::class => FavoritePolicy::class,
];


    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
    \Illuminate\Support\Facades\Gate::define('update', function ($user, $book) {
        return $user->id === $book->user_id;
    });
    \Illuminate\Support\Facades\Gate::define('delete', function ($user, $book) {
    return $user->id === $book->user_id;
});

    }
}
