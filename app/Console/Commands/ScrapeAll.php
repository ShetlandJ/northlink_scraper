<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScrapeAll extends Command
{
    // signature
    protected $signature = 'scrape:all';

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
        ini_set('max_execution_time', 0);

        $s1 = microtime(true);
        // run ScrapeOnePaxData
        $this->info('Running ScrapeOnePaxData');
        $this->call('scrape:pax-1');
        $e1 = microtime(true);

        $s2 = microtime(true);
        // run ScrapeCarDataOnePax
        $this->info('Running ScrapeCarDataOnePax');
        $this->call('scrape:car-1');
        $e2 = microtime(true);

        $s3 = microtime(true);
        // run GetTripAccommodation
        $this->info('Running GetTripAccommodation');
        $this->call('scrape:accom');
        $e3 = microtime(true);

        $this->info('ScrapeOnePaxData completed in ' . ($e1 - $s1) . ' seconds');
        $this->info('ScrapeCarDataOnePax completed in ' . ($e2 - $s2) . ' seconds');
        $this->info('GetTripAccommodation completed in ' . ($e3 - $s3) . ' seconds');
    }
}
