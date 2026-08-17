<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\StoreLocatorService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Ronappleton\Tile38PhpClient\Clients\Tile38;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Tile38::class, function (): Tile38 {
            $client = new Tile38(
                config('tile38.host'),
                (int) config('tile38.port'),
            );

            $client->output('json');

            return $client;
        });

        $this->app->singleton(StoreLocatorService::class, function (mixed $app): StoreLocatorService {
            return new StoreLocatorService($app->make(Tile38::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
