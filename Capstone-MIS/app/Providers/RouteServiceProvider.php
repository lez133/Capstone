<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\EnsureBeneficiaryOtpVerified;


class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    public function boot(): void
    {

        Route::aliasMiddleware('beneficiary.otp', EnsureBeneficiaryOtpVerified::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->view('errors.too-many-requests', [
                    'message' => 'Too many login attempts. Please try again in 60 seconds.',
                ], 429);
            });
        });

        Log::info('Rate limiter [login] registered:', [
            'limiter' => RateLimiter::limiter('login'),
        ]);

        // Define the 'api' rate limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
