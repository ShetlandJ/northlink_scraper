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
    protected $signature = 'scrape:car-1 {routeCode}';

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
        $routeCodeArg = $this->argument('routeCode');

        $continueCounter = 0;

        $returnRoute = $this->getReturnRoute($routeCodeArg);

        $payload = $this->configService->formatRequest(
            $routeCodeArg,
            $returnRoute,
            date('Y-m-d', strtotime("+1 day")),
            date('Y-m-d', strtotime("+5 days")),
            $paxAmount = "1",
            $vehicleCode = 'CAR'
        );

        $this->northlinkService->fetchToken($payload);

        $dates = $this->createDatesArray();

        $jobRun = $this->jobRunService->findByJobNameOrCreate('ScrapeCarDataOnePax', $routeCodeArg);

        $this->jobRunService->startJob($jobRun);

            $bar = $this->output->createProgressBar(count($dates));

            $continueCounter = 0;
            foreach ($dates as $dateString) {
                if ($continueCounter > 5) {
                    $this->exit();
                }

                try {
                    $data = $this->northlinkService->fetchDataByDate($dateString, $routeCodeArg);
                    if (!$data) {
                        $continueCounter++;
                        continue;
                    }
                } catch (\Exception $e) {
                    $continueCounter++;
                    continue;
                }

                $continueCounter = 0;

                $this->northlinkService->updateVehicleAvailabilityStatus($data, $dateString, $routeCodeArg);

                $bar->advance();
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

    private function getReturnRoute($routeCodeArg): string
    {
        if ($routeCodeArg === Trip::LERWICK_TO_ABERDEEN) {
            return Trip::ABERDEEN_TO_LERWICK;
        }
        if ($routeCodeArg === Trip::ABERDEEN_TO_LERWICK) {
            return Trip::LERWICK_TO_ABERDEEN;
        }
        if ($routeCodeArg === Trip::ABERDEEN_TO_KIRKWALL) {
            return Trip::KIRKWALL_TO_ABERDEEN;
        }
        if ($routeCodeArg === Trip::KIRKWALL_TO_ABERDEEN) {
            return Trip::ABERDEEN_TO_KIRKWALL;
        }
        if ($routeCodeArg === Trip::KIRKWALL_TO_LERWICK) {
            return Trip::LERWICK_TO_KIRKWALL;
        }
        if ($routeCodeArg === Trip::LERWICK_TO_KIRKWALL) {
            return Trip::KIRKWALL_TO_LERWICK;
        }
        if ($routeCodeArg === Trip::SCRABSTER_TO_STROMNESS) {
            return Trip::STROMNESS_TO_SCRABSTER;
        }
        if ($routeCodeArg === Trip::STROMNESS_TO_SCRABSTER) {
            return Trip::SCRABSTER_TO_STROMNESS;
        }


        return '';
    }
}
