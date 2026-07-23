<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CoverageZone;
use Illuminate\Support\Facades\Cache;

class CheckCoverageExpiryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coverage:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate coverage zones that have passed their effective_to date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredZones = CoverageZone::where('status', 'ACTIVE')
            ->whereNotNull('effective_to')
            ->where('effective_to', '<', now())
            ->get();

        if ($expiredZones->isEmpty()) {
            $this->info('No expired coverage zones found.');
            return;
        }

        foreach ($expiredZones as $zone) {
            $zone->update(['status' => 'INACTIVE']);
            $this->info("Deactivated Coverage Zone ID: {$zone->id}");
        }

        // Invalidating cache is handled by CoverageZone's 'saved' eloquent event
        
        $this->info('Expiry check completed successfully.');
    }
}
