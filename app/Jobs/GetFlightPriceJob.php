<?php

namespace App\Jobs;

use App\Models\FlightPrice;
use Illuminate\Bus\Queueable;
use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class GetFlightPriceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private string $date,
        private string $departureAirportCode,
        private string $arrivalAirportCode
    ) {
        $this->date = $date;
        $this->departureAirportCode = $departureAirportCode;
        $this->arrivalAirportCode = $arrivalAirportCode;
    }

    public function handle(): array
    {
        $puppeteer = new Puppeteer();
        $browser = $puppeteer->launch([
            'headless' => true
            // 'args' => [
            //     '--user-agent="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3312.0 Safari/537.36"'
            // ]
        ]);

        $page = $browser->newPage();
        $ua = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36";
        $page->setUserAgent($ua);
        $page->goto('https://www.loganair.co.uk/');

        $page->waitForSelector('#Origin');
        $page->click('#Origin');
        $page->select('#Origin', $this->departureAirportCode);
        $page->select('#Destination', $this->arrivalAirportCode);

        $page->select('#JourneyType', "0");

        $page->click('#OutboundDate');

        $dateTimeString = new \DateTime($this->date);
        $dayNumber = $dateTimeString->format("d");

        $page->evaluate(JsFunction::createWithBody("
                const elements = Array.from(document.querySelectorAll('span.qs-num'));
                const el = elements.find((el) => el.innerText === String($dayNumber));
                return el.parentElement.click();
            "));

        $page->click('.flight-search__cta > button');

        $page->waitForNavigation();

        $page->reload();

        $flights = $page->evaluate(JSFunction::createWithBody("
                const fltRowElements = document.querySelectorAll('[id^=\"flt_row_0_\"]');

                let numFltRows = 0;

                fltRowElements.forEach(element => {
                    numFltRows++;
                });

                const fltRows = [];

                for (let i = 0; i < numFltRows; i++) {
                    const elClass = \"#flt_row_0_\" + i;
                    const spanElements = document.querySelectorAll('#flt_row_0_' + i + ' span.time');
                    console.log(spanElements, '#flt_row_0_' + i + ' span.time');
                    const times = [];
                    let price = 0;

                    spanElements.forEach(element => {
                        times.push(element.innerText);
                    });

                    const priceElement = document.querySelector('#flt_row_0_' + i + ' div.fare-price');
                    if (priceElement) {
                        price = priceElement.innerText;
                    }

                    fltRows.push({times, price});
                }

                console.log(fltRows);

                return fltRows;
            "));

        logger(["1", $flights]);
        foreach ($flights as $flight) {
            $foundFlight = FlightPrice::where('departure_airport', $this->departureAirportCode)
                ->where('arrival_airport', $this->arrivalAirportCode)
                ->where('departure_date', $this->date)
                ->where('departure_time', $flight['times'][0])
                ->first();
            logger("2");

            if ($foundFlight) {
                logger("3");
                $foundFlight->update([
                    'arrival_time' => $flight['times'][1],
                    'price' => floatval(str_replace("£", "", $flight['price']))
                ]);
            } else {
                logger("4");
                $flightPrice = new FlightPrice();

                $flightPrice->departure_airport = $this->departureAirportCode;
                $flightPrice->arrival_airport = $this->arrivalAirportCode;
                $flightPrice->departure_date = $this->date;
                $flightPrice->departure_time = $flight['times'][0];
                $flightPrice->arrival_time = $flight['times'][1];
                $flightPrice->price = floatval(str_replace("£", "", $flight['price']));

                $flightPrice->save();
                logger("5");
            }
        }

        logger("6");

        $browser->close();

        return $flights;
    }
}
