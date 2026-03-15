<?php

namespace App\Console\Commands;

use App\Jobs\CheckWebsite;
use App\Models\Website;
use Illuminate\Console\Command;

class DispatchSiteChecks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sites:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch uptime check jobs for all active websites';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $websites = Website::all();

        $this->info("Dispatching checks for {$websites->count()} website(s)...");

        $websites->each(fn ($website) => CheckWebsite::dispatch($website));

        $this->info('Done.');
    }
}
