<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\Review;
use App\Policies\BookPolicy;
use App\Policies\ReadingPlanPolicy;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Book::class => BookPolicy::class,
        Review::class => ReviewPolicy::class,
        ReadingPlan::class => ReadingPlanPolicy::class,
    ];

    public function boot(): void
    {
        Gate::define('update', function ($user, $book) {
            return $user->id === $book->user_id;
        });
        Gate::define('delete', function ($user, $book) {
            return $user->id === $book->user_id;
        });

    }
}
