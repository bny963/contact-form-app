<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\CreatesNewUsers::class,
            \App\Actions\Fortify\CreateNewUser::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 💡 1. 登録画面（/register）で表示するBladeを指定
        Fortify::registerView(function () {
            return view('auth.register'); // resources/views/auth/register.blade.php
        });

        // 💡 2. ログイン画面（/login）で表示するBladeを指定
        Fortify::loginView(function () {
            return view('auth.login');    // resources/views/auth/login.blade.php
        });

        // 〜〜 ここから下は元々あるレートリミットの設定（消さない） 〜〜
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(5)->by($email . $request->ip());
        });
    }
}
