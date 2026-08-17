<?php

declare(strict_types=1);

use App\Services\StoreLocatorService;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\FakeStoreLocatorService;

beforeEach(function (): void {
    $this->app->singleton(StoreLocatorService::class, fn (): FakeStoreLocatorService => new FakeStoreLocatorService);
});

test('the showcase page renders the million-store proof', function (): void {
    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Showcase')
        ->where('initial.count', 1_000_000)
        ->where('initial.tile38_version', '1.38.0'));
});
