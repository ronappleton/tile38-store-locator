<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\StoreLocatorService;
use Composer\InstalledVersions;
use Inertia\Response;
use Inertia\ResponseFactory;

class ShowcaseController
{
    public function __invoke(StoreLocatorService $stores, ResponseFactory $inertia): Response
    {
        return $inertia->render('Showcase', [
            'initial' => [
                'count' => $stores->count(),
                'tile38_version' => $stores->clientVersion() ?? 'unknown',
                'client_version' => InstalledVersions::getPrettyVersion('ronappleton/tile38-php-client'),
                'center' => ['lat' => 39.7392, 'lng' => -104.9903],
            ],
        ]);
    }
}
