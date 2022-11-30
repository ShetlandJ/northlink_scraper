<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\Token;
use App\Models\JobRun;
use App\Services\ConfigService;
use App\Services\JobRunService;
use Illuminate\Console\Command;
use App\Services\NorthlinkService;

class ScrapeCarDataOnePax extends Command
{
    // signature
    protected $signature = 'scrape:car-1';

    // description
    protected $description = 'Scrape car data from Northlink';

    // northlink service
    private NorthlinkService $northlinkService;
    private ConfigService $configService;
    private JobRunService $jobRunService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        NorthlinkService $northlinkService,
        ConfigService $configService,
        JobRunService $jobRunService
    ) {
        parent::__construct();
        $this->northlinkService = $northlinkService;
        $this->configService = $configService;
        $this->jobRunService = $jobRunService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // give users an option to choose between ABLE or LEAB
        // $routeCode = $this->choice('Which token do you want to use?', ['ABLE', 'LEAB']);

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

        $jobRun = $this->jobRunService->findByJobNameOrCreate('ScrapeCarDataOnePax');
        $this->jobRunService->startJob($jobRun);

        foreach (['LEAB', 'ABLE'] as $routeCode) {
            $bar = $this->output->createProgressBar(count($dates));

            $continueCounter = 0;
            foreach ($dates as $dateString) {
                if ($continueCounter > 5) {
                    $this->exit();
                }

                try {
                    $data = $this->northlinkService->fetchDataByDate($dateString, $routeCode);
                    if (!$data) {
                        $continueCounter++;
                        continue;
                    }
                } catch (\Exception $e) {
                    $continueCounter++;
                    continue;
                }

                $continueCounter = 0;

                $this->northlinkService->updateVehicleAvailabilityStatus($data, $dateString, $routeCode);
                // advance progress bar
                $bar->advance();
            }
        }

        $this->jobRunService->endJob($jobRun);

        return 0;
    }

    private function createDatesArray(): array
    {
        $dates = [];
        $startDate = date('Y-m-d');
        $endDate = '2023-04-01';
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $dates[] = $currentDate;
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        return $dates;
    }
}
