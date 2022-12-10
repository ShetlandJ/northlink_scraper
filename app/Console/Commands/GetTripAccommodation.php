<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Trip;
use App\Models\Token;
use App\Models\JobRun;
use App\Services\ConfigService;
use App\Services\JobRunService;
use Illuminate\Console\Command;
use App\Services\NorthlinkService;

class GetTripAccommodation extends Command
{
    // signature
    protected $signature = 'scrape:accom {routeCode}';

    // description
    protected $description = 'Syncs available accommodation with per trip route';

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
        $routeCodeArg = $this->argument('routeCode');

        if ($routeCodeArg) {
            $this->error('Route code is required');
        }

        $returnRoute = $this->getReturnRoute($routeCodeArg);

        // give users an option to choose between ABLE or LEAB
        $continueCounter = 0;

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

        $jobRun = $this->jobRunService->findByJobNameOrCreate('GetTripAccommodation');
        $this->jobRunService->startJob($jobRun);

        $this->info("Fetching accommodation for $routeCodeArg");
        $bar = $this->output->createProgressBar(count($dates));

        foreach ($dates as $dateString) {
            if ($continueCounter > 5) {
                $this->exit();
            }

            try {
                $this->northlinkService->fetchAccomodation(
                    $dateString,
                    $routeCodeArg,
                    $returnRoute
                );
            } catch (Exception $e) {
                $continueCounter++;
                continue;
            }

            $continueCounter = 0;

            $bar->advance();
        }

        $bar->finish();

        $this->jobRunService->endJob($jobRun);

        return 0;
    }

    private function createDatesArray(): array
    {
        $dates = [];
        $startDate = date("Y-m-d", strtotime('tomorrow'));
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
