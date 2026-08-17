<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Services\StoreLocatorService;

class FakeStoreLocatorService extends StoreLocatorService
{
    public function __construct() {}

    public function count(): int
    {
        return 1_000_000;
    }

    public function clientVersion(): ?string
    {
        return '1.38.0';
    }

    public function nearest(float $lat, float $lng, int $limit, array $filters = []): array
    {
        return [
            'command' => 'NEARBY',
            'elapsed_ms' => 0.42,
            'count' => 1,
            'results' => [[
                'id' => 'store-0000001',
                'lat' => $lat,
                'lng' => $lng,
                'distance' => 0.0,
                'fields' => ['city' => 'Denver', 'category' => 1, 'open' => 1, 'rating' => 4.5],
            ]],
        ];
    }

    public function within(float $south, float $west, float $north, float $east, int $limit, array $filters = []): array
    {
        return [
            'command' => 'WITHIN',
            'elapsed_ms' => 0.31,
            'count' => 0,
            'results' => [],
        ];
    }

    public function benchmark(float $lat, float $lng, int $limit, int $runs, array $filters = []): array
    {
        return ['runs' => $runs, 'total_ms' => 2.1, 'min_ms' => 0.3, 'average_ms' => 0.42, 'stores' => 1_000_000];
    }
}
