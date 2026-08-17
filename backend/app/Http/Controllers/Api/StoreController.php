<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\StoreLocatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StoreController
{
    public function index(Request $request, StoreLocatorService $stores): JsonResponse
    {
        $data = Validator::make($request->query(), [
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'category' => ['nullable', 'integer', 'min:0', 'max:4'],
            'open' => ['nullable', 'boolean'],
        ])->validate();

        return response()->json($stores->nearest(
            (float) $data['lat'],
            (float) $data['lng'],
            (int) ($data['limit'] ?? 10),
            array_filter([
                'category' => $data['category'] ?? null,
                'open' => $data['open'] ?? null,
            ], static fn (mixed $value): bool => $value !== null),
        ));
    }

    public function viewport(Request $request, StoreLocatorService $stores): JsonResponse
    {
        $data = Validator::make($request->all(), [
            'south' => ['required', 'numeric'],
            'west' => ['required', 'numeric'],
            'north' => ['required', 'numeric'],
            'east' => ['required', 'numeric'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:500'],
        ])->validate();

        return response()->json($stores->within(
            (float) $data['south'],
            (float) $data['west'],
            (float) $data['north'],
            (float) $data['east'],
            (int) ($data['limit'] ?? 200),
        ));
    }

    public function benchmark(Request $request, StoreLocatorService $stores): JsonResponse
    {
        $data = Validator::make($request->query(), [
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'runs' => ['nullable', 'integer', 'min:1', 'max:25'],
        ])->validate();

        return response()->json($stores->benchmark(
            (float) ($data['lat'] ?? 39.7392),
            (float) ($data['lng'] ?? -104.9903),
            (int) ($data['limit'] ?? 10),
            (int) ($data['runs'] ?? 5),
        ));
    }
}
