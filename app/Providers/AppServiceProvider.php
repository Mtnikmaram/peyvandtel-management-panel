<?php

namespace App\Providers;

use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('apiError', function (string $name, string $reason) {
            return Response::json(['errors' => [$name ?? 'error' => [$reason]]], Response::HTTP_UNPROCESSABLE_ENTITY);
        });
    }
}
