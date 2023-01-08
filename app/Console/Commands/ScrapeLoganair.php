<?php

namespace App\Console\Commands;

use DateTime;
use App\Models\Trip;
use App\Models\Token;
use App\Models\JobRun;
use Nesk\Puphpeteer\Puppeteer;
use App\Services\ConfigService;
use App\Services\JobRunService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Pool;
use App\Services\NorthlinkService;
use Illuminate\Support\Facades\Http;
use Nesk\Rialto\Data\JsFunction;

class ScrapeLoganair extends Command
{
    // signature
    protected $signature = 'scrape:loganair';

    // description
    protected $description = 'Scrape Loganair data';

    // northlink service
    private NorthlinkService $northlinkService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        NorthlinkService $northlinkService,
    ) {
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
        $puppeteer = new Puppeteer();
        $browser = $puppeteer->launch([
            'headless' => false,
            'devtools' => true,
        ]);

        $page = $browser->newPage();
        $page->goto('https://www.loganair.co.uk/');

        $page->waitForSelector('#Origin');
        $page->click('#Origin');
        $page->select('#Origin', 'ABZ');
        $page->select('#Destination', 'LSI');

        $page->select('#JourneyType', "0");

        $page->click('#OutboundDate');

        $tomorrow = new DateTime();
        $tomorrow->add(new \DateInterval('P1D'));
        $tomorrowDate = $tomorrow->format('d');
        // dd("JAMES");
        // dd($tomorrowDate);

        // $page->evaluate(function ($tomorrowDate) {
//     $elements = [];
//     foreach (document.querySelectorAll('span.qs-num') as $element) {
//         $elements[] = $element;
//     }
//     $el = null;
//     foreach ($elements as $element) {
//         if ($element->innerText == $tomorrowDate) {
//             $el = $element;
//             break;
//         }
//     }
//     if ($el) {
//         $el->parentElement->click();
//     }
        // }, $tomorrowDate);
        $page->evaluate(JsFunction::createWithBody("
            const elements = Array.from(document.querySelectorAll('span.qs-num'));
            const el = elements.find((el) => el.innerText === String($tomorrowDate));
            return el.parentElement.click();
        "));

        $page->click('.flight-search__cta > button');

        $page->waitForNavigation();

        $page->reload();
        // $button = $page->$x("//button[contains(., 'Find flights')]");

        // $button[0]->click();
        sleep(15);
        // $page->reload();

        // $browser->close();
    }
}
