<?php

namespace Modules\Note\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Note\Domain\Contracts\NoteRepositoryInterface;
use Modules\Note\Infrastructure\Repositories\EloquentNoteRepository;

class NoteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NoteRepositoryInterface::class, EloquentNoteRepository::class);
    }

    public function boot(): void
    {
        Route::prefix('api')->group(__DIR__ . '/../Routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
    }
}
