<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // Factory resolution for Module models (ModelName → ModelNameFactory)
        \Illuminate\Database\Eloquent\Factories\Factory::guessFactoryNamesUsing(function (string $modelName) {
            $baseName = class_basename($modelName);
            // Strip 'Model' suffix: UserModel → User, NoteModel → Note
            $name = preg_replace('/Model$/', '', $baseName);

            return 'Database\\Factories\\' . $name . 'Factory';
        });

        // Password security defaults
        Password::defaults(function () {
            $rule = Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols();

            if (! $this->app->environment('testing')) {
                $rule->uncompromised();
            }

            return $rule;
        });

        // Rate limiters
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
