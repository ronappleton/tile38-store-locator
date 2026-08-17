# tile38-store-locator

A live million-store locator showcase for
[`ronappleton/tile38-php-client`](https://github.com/ronappleton/tile38-php-client).
The map proves that Tile38 can search a huge spatial collection without sending
one million markers to the browser.

## Quick start

```bash
make up
```

Open [http://localhost:8000](http://localhost:8000). The first boot loads
1,000,000 synthetic stores into Tile38 through the client's phpredis pipeline
helper. The dataset is persisted in the `tile38data` volume.

Useful commands:

```bash
make seed COUNT=1000000
make benchmark
make down
```

Requirements: Docker, Docker Compose, and `jq` for the benchmark command.

## What it demonstrates

- Nearest-N lookup with `NEARBY`, `LIMIT`, and a radiusless `POINT`.
- Viewport lookup with `WITHIN` and map bounds.
- Numeric `WHERE` filters for open stores and categories.
- Bulk loading through `Tile38::pipeline()`.
- Measured server-side lookup latency against one million indexed points.
- MapLibre rendering of only the returned answer set, never the full dataset.

The stores are synthetic and clearly labeled. The benchmark measures the live
Tile38 instance in Docker; it is not a claim about every production workload.

## Layout

```text
backend/            Laravel 13 app (Inertia + Vue 3 + TypeScript)
docker/php/         PHP 8.5 runtime image with ext-redis
docker-compose.yml  Tile38, app, and one-shot 1M-store seeder
Makefile            Local development and benchmark commands
```

## Query path

```text
browser click / map movement
          |
          v
Laravel API -> tile38-php-client -> Tile38 spatial index
          |
          v
small measured result set -> MapLibre markers + latency HUD
```

The browser receives at most 12 nearest results or 120 viewport results. The
million-store collection stays inside Tile38.

## Development

| Command                   | What it does                                     |
| ------------------------- | ------------------------------------------------ |
| `make up`                 | Build and boot Tile38, Laravel, and the seeder   |
| `make seed COUNT=1000000` | Reload the synthetic dataset                     |
| `make benchmark`          | Run five nearest-store queries and print timings |
| `make test`               | Run backend and frontend tests                   |
| `make ci`                 | Run style, types, tests, and frontend checks     |
| `make down`               | Stop all containers                              |

## Client dependency

The showcase uses `ronappleton/tile38-php-client` v1.2+, including its
`pipeline()` helper. The nearest-N query uses the existing Tile38 command
surface:

```php
$client->nearby('stores', Point::make($lat, $lng))
    ->limit(10)
    ->distance()
    ->points()
    ->execute();
```
