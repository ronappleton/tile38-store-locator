<?php

declare(strict_types=1);

namespace App\Services;

use Ronappleton\Tile38PhpClient\Clients\Tile38;
use Ronappleton\Tile38PhpClient\Commands\Objects\Bounds;
use Ronappleton\Tile38PhpClient\Commands\Objects\Point;

use function array_map;
use function array_values;
use function count;
use function hrtime;
use function is_array;
use function json_decode;
use function max;
use function mt_rand;
use function round;

class StoreLocatorService
{
    private const KEY = 'stores';

    /**
     * Synthetic population centres used to make the dataset look like a real
     * retail footprint instead of a uniform rectangle.
     *
     * @var array<int, array{city: string, lat: float, lng: float}>
     */
    private const CITIES = [
        ['city' => 'New York', 'lat' => 40.7128, 'lng' => -74.0060],
        ['city' => 'Los Angeles', 'lat' => 34.0522, 'lng' => -118.2437],
        ['city' => 'Chicago', 'lat' => 41.8781, 'lng' => -87.6298],
        ['city' => 'Houston', 'lat' => 29.7604, 'lng' => -95.3698],
        ['city' => 'Phoenix', 'lat' => 33.4484, 'lng' => -112.0740],
        ['city' => 'Seattle', 'lat' => 47.6062, 'lng' => -122.3321],
        ['city' => 'Denver', 'lat' => 39.7392, 'lng' => -104.9903],
        ['city' => 'Miami', 'lat' => 25.7617, 'lng' => -80.1918],
    ];

    private readonly Tile38 $client;

    public function __construct(Tile38 $client)
    {
        $this->client = $client;
    }

    public function clientVersion(): ?string
    {
        try {
            $info = $this->decode((string) $this->client->info()->execute());

            return isset($info['info']['tile38_version'])
                ? (string) $info['info']['tile38_version']
                : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function reset(): void
    {
        $this->client->drop(self::KEY)->execute();
    }

    public function count(): int
    {
        try {
            $result = $this->decode((string) $this->client->scan(self::KEY)->count()->execute());

            return (int) ($result['count'] ?? 0);
        } catch (\Throwable) {
            return 0;
        }
    }

    public function seed(int $count, ?callable $progress = null): int
    {
        if ($this->count() >= $count) {
            return $this->count();
        }

        $this->reset();
        mt_srand(3801);

        $batchSize = 1000;

        for ($offset = 0; $offset < $count; $offset += $batchSize) {
            $end = min($count, $offset + $batchSize);

            $this->client->pipeline(function (Tile38 $client) use ($offset, $end): void {
                for ($index = $offset; $index < $end; $index++) {
                    [$city, $lat, $lng] = $this->storeLocation($index);
                    $category = $index % 5;
                    $open = $index % 11 === 0 ? 0 : 1;

                    $client->set(
                        self::KEY,
                        sprintf('store-%07d', $index + 1),
                        Point::make($lat, $lng),
                    )
                        ->field('city', $city)
                        ->field('category', $category)
                        ->field('open', $open)
                        ->field('rating', 3 + ($index % 20) / 10)
                        ->execute();
                }
            });

            if ($progress !== null) {
                $progress($end, $count);
            }
        }

        return $count;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{command: string, elapsed_ms: float, count: int, results: array<int, array<string, mixed>>}
     */
    public function nearest(float $lat, float $lng, int $limit, array $filters = []): array
    {
        $started = hrtime(true);
        $command = $this->client->nearby(self::KEY, Point::make($lat, $lng));

        $this->applyFilters($command, $filters);
        $result = $this->decode((string) $command->limit($limit)->distance()->points()->execute());

        return $this->response('NEARBY', $started, $this->normalisePoints($result));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{command: string, elapsed_ms: float, count: int, results: array<int, array<string, mixed>>}
     */
    public function within(
        float $south,
        float $west,
        float $north,
        float $east,
        int $limit,
        array $filters = [],
    ): array {
        $started = hrtime(true);
        $command = $this->client->within(self::KEY, Bounds::make($south, $west, $north, $east));

        $this->applyFilters($command, $filters);
        $result = $this->decode((string) $command->limit($limit)->points()->execute());

        return $this->response('WITHIN', $started, $this->normalisePoints($result));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{runs: int, total_ms: float, min_ms: float, average_ms: float, stores: int}
     */
    public function benchmark(float $lat, float $lng, int $limit, int $runs, array $filters = []): array
    {
        $times = [];

        for ($run = 0; $run < $runs; $run++) {
            $result = $this->nearest($lat, $lng, $limit, $filters);
            $times[] = $result['elapsed_ms'];
        }

        $total = array_sum($times);
        sort($times);

        return [
            'runs' => $runs,
            'total_ms' => round($total, 3),
            'min_ms' => round($times[0] ?? 0.0, 3),
            'average_ms' => round($total / max(1, $runs), 3),
            'stores' => $this->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(mixed $command, array $filters): void
    {
        if (isset($filters['category'])) {
            $command->where('category', (int) $filters['category'], (int) $filters['category']);
        }

        if (isset($filters['open'])) {
            $open = (int) $filters['open'];
            $command->where('open', $open, $open);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<int, array<string, mixed>>
     */
    private function normalisePoints(array $result): array
    {
        $points = $result['points'] ?? [];

        if (! is_array($points)) {
            return [];
        }

        return array_values(array_map(
            fn (array $point): array => $this->normalisePoint($point),
            $points,
        ));
    }

    /**
     * @param  array<string, mixed>  $point
     * @return array<string, mixed>
     */
    private function normalisePoint(array $point): array
    {
        return [
            'id' => (string) ($point['id'] ?? ''),
            'lat' => (float) data_get($point, 'point.lat', 0),
            'lng' => (float) data_get($point, 'point.lon', 0),
            'distance' => data_get($point, 'distance'),
            'fields' => data_get($point, 'fields', []),
        ];
    }

    /**
     * @return array{0: string, 1: float, 2: float}
     */
    private function storeLocation(int $index): array
    {
        $city = self::CITIES[$index % count(self::CITIES)];
        $latJitter = (mt_rand(0, 10000) / 10000 - 0.5) * 1.2;
        $lngJitter = (mt_rand(0, 10000) / 10000 - 0.5) * 1.6;

        return [$city['city'], $city['lat'] + $latJitter, $city['lng'] + $lngJitter];
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     * @return array{command: string, elapsed_ms: float, count: int, results: array<int, array<string, mixed>>}
     */
    private function response(string $command, int $started, array $results): array
    {
        return [
            'command' => $command,
            'elapsed_ms' => round((hrtime(true) - $started) / 1_000_000, 3),
            'count' => count($results),
            'results' => $results,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $payload): array
    {
        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : [];
    }
}
