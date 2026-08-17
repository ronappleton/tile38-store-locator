<?php

declare(strict_types=1);

use App\Services\StoreLocatorService;
use Tests\Support\FakeStoreLocatorService;

beforeEach(function (): void {
    $this->app->singleton(StoreLocatorService::class, fn (): FakeStoreLocatorService => new FakeStoreLocatorService);
});

test('nearest endpoint returns the indexed answer and latency', function (): void {
    $this->getJson('/api/stores?lat=39.7392&lng=-104.9903&limit=10')
        ->assertOk()
        ->assertJsonPath('command', 'NEARBY')
        ->assertJsonPath('results.0.id', 'store-0000001')
        ->assertJsonPath('elapsed_ms', 0.42);
});

test('viewport endpoint validates bounds', function (): void {
    $this->postJson('/api/stores/viewport', [])->assertUnprocessable();
});

test('benchmark endpoint reports the indexed population', function (): void {
    $this->getJson('/api/stores/benchmark')->assertOk()->assertJson([
        'runs' => 5,
        'average_ms' => 0.42,
        'stores' => 1_000_000,
    ]);
});
