<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\StoreLocatorService;
use Illuminate\Console\Command;

class StoresSeedCommand extends Command
{
    protected $signature = 'stores:seed
        {--count=1000000 : Number of synthetic stores to load}
        {--force : Drop the existing collection before loading}';

    protected $description = 'Load the synthetic million-store Tile38 dataset.';

    public function handle(StoreLocatorService $stores): int
    {
        if ($this->option('force')) {
            $stores->reset();
        }

        $count = (int) $this->option('count');
        $started = microtime(true);

        $loaded = $stores->seed($count, function (int $current, int $total): void {
            if ($current % 10000 === 0 || $current === $total) {
                $this->output->write(sprintf("\rLoading stores: %d/%d", $current, $total));
            }
        });

        $elapsed = (microtime(true) - $started) * 1000;
        $this->newLine();
        $this->info(sprintf('Loaded %d stores in %.1f ms.', $loaded, $elapsed));

        return self::SUCCESS;
    }
}
