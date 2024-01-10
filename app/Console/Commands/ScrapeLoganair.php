<?php

namespace App\Console\Commands;

use DateTime;
use App\Models\Trip;
use App\Models\Token;
use App\Models\JobRun;
use App\Models\FlightPrice;
use Nesk\Puphpeteer\Puppeteer;
use App\Services\ConfigService;
use App\Services\JobRunService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use Nesk\Rialto\Data\JsFunction;
use App\Services\LoganairService;
use App\Services\NorthlinkService;
use Illuminate\Support\Facades\Http;
use App\Jobs\GetFlightPriceJob;
use Illuminate\Foundation\Bus\PendingDispatch;

class ScrapeLoganair extends Command
{
    private const SUMBURGH = 'LSI';
    private const ABERDEEN = 'ABZ';
    private const KIRKWALL = 'KOI';

    // signature
    protected $signature = 'scrape:loganair {one}';

    // description
    protected $description = 'Scrape Loganair data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // get {one} argument
        $one = $this->argument('one');

        $dates = [];

        if ($one) {
            $dates[] = $this->getDates()[0];
        } else {
            $dates = $this->getDates();
        }

        foreach ($dates as $dateString) {
            try {
                GetFlightPriceJob::dispatchNow($dateString, self::SUMBURGH, self::ABERDEEN);
            } catch (\Exception $e) {
                logger($e->getMessage());
                $this->error($e->getMessage());
            }
        }
    }

    public function getDates(): array
    {
        date_default_timezone_set('UTC');
        $start = new DateTime('tomorrow');
        $end = new DateTime('last day of this month');
        $dates = array();
        $dates[] = $start->format('Y-m-d');

        while ($start < $end) {
            $start->modify('+1 day');

            $dates[] = $start->format('Y-m-d');
        }

        return $dates;
    }
}
