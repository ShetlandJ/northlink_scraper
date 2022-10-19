<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\Token;
use App\Services\ConfigService;
use App\Services\NorthlinkService;
use Illuminate\Console\Command;

class ScrapeCarDataOnePax extends Command
{
    // signature
    protected $signature = 'scrape:car-2';

    // description
    protected $description = 'Scrape car data from Northlink';

    // northlink service
    private NorthlinkService $northlinkService;
    private ConfigService $configService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        NorthlinkService $northlinkService,
        ConfigService $configService
    ) {
        parent::__construct();
        $this->northlinkService = $northlinkService;
        $this->configService = $configService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // give users an option to choose between ABLE or LEAB
        $routeCode = $this->choice('Which token do you want to use?', ['ABLE', 'LEAB']);

        $continueCounter = 0;

        $payload = $this->configService->formatRequest(
            Trip::LERWICK_TO_ABERDEEN,
            TRIP::ABERDEEN_TO_LERWICK,
            date('Y-m-d', strtotime("+1 day")),
            date('Y-m-d', strtotime("+5 days")),
            $paxAmount = "1",
            $vehicleCode = 'CAR'
        );

        $this->northlinkService->fetchToken($payload);

        $dates = $this->createDatesArray();

        $bar = $this->output->createProgressBar(count($dates));

        foreach ($dates as $dateString) {
            if ($continueCounter > 5) {
                $this->exit();
            }

            $data = $this->northlinkService->fetchDataByDate($dateString, $routeCode);
            if (!$data) {
                $continueCounter++;
                continue;
            }
            $this->northlinkService->updateOrCreateTripRecords($data, $dateString, $routeCode);
            // advance progress bar
            $bar->advance();
        }


        return 0;
    }

    private function createDatesArray(): array
    {
        $dates = [];
        $startDate = date('Y-m-d');
        $endDate = '2022-12-30';
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        return $dates;
    }
}