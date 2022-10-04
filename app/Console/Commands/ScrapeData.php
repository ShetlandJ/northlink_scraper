<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\Token;
use App\Services\NorthlinkService;
use Illuminate\Console\Command;

class ScrapeData extends Command
{
    // signature
    protected $signature = 'scrape:data';

    // description
    protected $description = 'Scrape data from Northlink';

    // northlink service
    private NorthlinkService $northlinkService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NorthlinkService $northlinkService)
    {
        parent::__construct();
        $this->northlinkService = $northlinkService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $continueCounter = 0;
        $this->northlinkService->fetchToken();
        // create array of dates from 2022-10-05 to 2022-12-30
        $dates = $this->createDatesArray();

        foreach ($dates as $dateString) {
            if ($continueCounter > 5) {
                $this->exit();
            }

            // if trip exists for date, skip
            if (Trip::where('date', $dateString)->exists()) {
                logger("Trip exists for date: {$dateString}");
                continue;
            }

            logger(sprintf('Scraping data for date: %s', $dateString));
            $data = $this->northlinkService->fetchDataByDate($dateString);
            if (!$data) {
                $continueCounter++;
                continue;
            }
            $this->northlinkService->updateOrCreateTripRecords($data, $dateString);
            logger(sprintf('Finished scraping data for date: %s', $dateString));
            logger("-------------------------------");
        }


        return 0;
    }

    private function createDatesArray(): array
    {
        $dates = [];
        $startDate = '2022-10-05';
        $endDate = '2022-12-30';
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        return $dates;
    }
}