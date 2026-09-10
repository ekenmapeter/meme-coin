<?php

namespace App\Console\Commands;

use App\Services\MarketSimulatorService;
use Illuminate\Console\Command;

class MarketTick extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'market:tick';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advance the simulated market: ticks all auto-movement coins.';

    /**
     * Execute the console command.
     */
    public function handle(MarketSimulatorService $simulator): int
    {
        $ticked = $simulator->tickAllCoins();
        $synced = $simulator->syncLiveCoinPrices();

        $this->info("Market tick complete. {$ticked} coin(s) simulated, {$synced} coin(s) synced from live prices.");

        return self::SUCCESS;
    }
}
