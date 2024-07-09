<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response;
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
            return Response::json(['errors' => [$name ?? 'error' => [$reason]]], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        });

        Json::encodeUsing(fn ($value) => json_encode(value: $value, flags: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
        Json::decodeUsing(fn ($value, $associative) => json_decode(json: $value, associative: $associative, flags: JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK));
    }
}
