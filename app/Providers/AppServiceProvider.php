<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Socialite;
use App\Providers\Oauth\LineProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('app.env') == 'production') {
            URL::forceScheme('https');
        }


        $this->bootLineSocialite();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    private function bootLineSocialite()
    {
        $socialite = $this->app->make('Laravel\Socialite\Contracts\Factory');
        $socialite->extend('line', function ($app) use ($socialite) {
            $config = $app['config']['services.line'];
            return $socialite->buildProvider(LineProvider::class, $config);
        });
    }
}
