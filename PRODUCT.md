# Product — tile38-store-locator

<!-- impeccable:product-schema 1 -->

## Platform

web

## Stack

Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS 4, MapLibre GL, Docker
Compose, Tile38 1.38.0, and `ronappleton/tile38-php-client` 1.2+. There is no
realtime transport: this showcase is intentionally about static spatial lookup
speed.

## Product purpose

A live proof that a spatial index can serve a nearest-store experience over
1,000,000 points without moving the dataset into application memory or the
browser. The map is evidence, not a marketing illustration.

## Audience

PHP and Laravel developers evaluating Tile38 for store locators, delivery radius
searches, field service, reverse geocoding, and other location-aware features.
They should understand the query, see its result, and inspect the exact client
call within a minute.

## Core experience

- Boot one million synthetic store points into a persistent Tile38 volume.
- Click the map to run a nearest-N query.
- Pan and zoom to run a viewport `WITHIN` query.
- Show returned markers, query type, result count, and measured milliseconds.
- Run a five-query benchmark against the live collection.
- Show the PHP fluent call beside the map.

## Truth constraints

- Store data is synthetic and explicitly labeled.
- The benchmark reports the local Docker instance, not a universal SLA.
- The browser never renders the full million-point collection.
- No real customer data, testimonials, pricing, or production claims.

## Success criteria

- First boot loads 1,000,000 stores through the client pipeline helper.
- Nearest and viewport queries return bounded result sets.
- The page remains responsive on desktop and mobile.
- Backend and frontend checks pass locally.
- The repository is public, in Codacy, and has zero active quality issues.
