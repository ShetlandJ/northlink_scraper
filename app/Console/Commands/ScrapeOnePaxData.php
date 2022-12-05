<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Models\Token;
use App\Models\JobRun;
use App\Services\ConfigService;
use App\Services\JobRunService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use App\Services\NorthlinkService;
use Illuminate\Support\Facades\Http;

class ScrapeOnePaxData extends Command
{
    // signature
    protected $signature = 'scrape:pax-1 {routeCode?}';

    // description
    protected $description = 'Scrape data from Northlink';

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
        ini_set('max_execution_time', 10000);

        $routeCodeArg = $this->argument('routeCode');

        // start timer
        $start = microtime(true);

        $continueCounter = 0;

        $returnRoute = $this->getReturnRoute($routeCodeArg);

        $payload = $this->configService->formatRequest(
            $routeCodeArg,
            $returnRoute,
            date('Y-m-d', strtotime("+1 day")),
            date('Y-m-d', strtotime("+5 days")),
            $paxAmount = "1",
        );

        $token = $this->northlinkService->fetchToken($payload);

        $dates = $this->createDatesArray();

        $jobRun = $this->jobRunService->findByJobNameOrCreate('ScrapeOnePaxData');
        $this->jobRunService->startJob($jobRun);

        $routes = Trip::ALL_ROUTES;

        if ($routeCodeArg) {
            $routes = [$routeCodeArg];
        }

        foreach ($routes as $routeCode) {
            $this->info('Scraping route ' . $routeCode);
            $responses = Http::pool(fn (Pool $pool) =>
                collect($dates)
                    ->map(
                        fn (string $date) => $pool->withHeaders([
                        'Authorization' => $token
                    ])->get(
                        $this->getUrl($routeCode, $date)
                    )));

            foreach ($responses as $index => $res) {
                $json = $res->getBody();
                $data = json_decode($json, true);
                $results = isset($data["res"]['result'][0]) ? $data["res"]['result'][0] : null;

                if (!$results) {
                    $continueCounter++;
                    continue;
                }
                $this->northlinkService->updateOrCreateTripRecords($results, $dates[$index], $routeCode);
            }

            $this->info($routeCode . ' scraping complete');
        }

        $end = microtime(true);

        $minutes = ($end - $start) / 60;
        $this->info(sprintf('The operation took %s minutes', $minutes));

        $this->jobRunService->endJob($jobRun);

        return 0;
    }

    private function getUrl($routeCode, $date): string
    {
        return sprintf(
            'https://www.northlinkferries.co.uk/api/departures/%s/prices/%s',
            $routeCode,
            $date,
        );
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
