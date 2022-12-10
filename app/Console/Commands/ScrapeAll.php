<?php

namespace App\Console\Commands;

use App\Models\Trip;
use Illuminate\Console\Command;

class ScrapeAll extends Command
{
    // signature
    protected $signature = 'scrape:all {routeCode}';

    // description
    protected $description = 'Runs all three main scrape commands';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $routeCode = $this->argument('routeCode');

        if (!$routeCode) {
            $this->error('Route code is required');
            return;
        }

        // run ScrapeOnePaxData
        $this->info('Running ScrapeOnePaxData');
        $this->call(sprintf('scrape:pax-1 %s', $routeCode));

        // run ScrapeCarDataOnePax
        $this->info('Running ScrapeCarDataOnePax');
        $this->call(sprintf('scrape:car-1 %s', $routeCode));

        // run GetTripAccommodation
        $this->info('Running GetTripAccommodation');
        $this->call(sprintf('scrape:accom %s', $routeCode));
    }
}
