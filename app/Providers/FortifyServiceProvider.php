<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 会員登録処理
        Fortify::createUsersUsing(CreateNewUser::class);

        // ログイン画面
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 会員登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });
    }
}
