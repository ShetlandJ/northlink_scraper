<?php

namespace App\Http\Controllers\Home;

use App\Models\Trip;
use Inertia\Inertia;
use Twilio\Rest\Client;
use App\Models\TripPrice;
use Illuminate\Http\Request;
use App\Services\NorthlinkService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NorthlinkController;

class TextDemoController extends NorthlinkController
{
    public function __invoke()
    {
        $year = (int) Route::input('year');
        $month = (int) Route::input('month');
        $routeCode = Route::input('routeCode');


        $firstDayOfMonth = date('Y-m-d', strtotime($year . '-' . $month . '-01'));

        $output = [];

        $output[$routeCode] = $this->getAvailableTrips($routeCode, $firstDayOfMonth);

        return $output;
    }

    private function getAvailableTrips(string $routeCode, string $firstDayOfMonth)
    {
        $trip = Trip::query();
        $trip->where('trips.date', '>=', $firstDayOfMonth);
        $trip->where('trips.date', '<=', date('Y-m-d', strtotime($firstDayOfMonth . ' + 1 month')));
        $trip->where('trips.routeCode', $routeCode);
        $trip->with('prices');

        $trips = $trip->get();

        $availableTrips = [];

        $send = false;
        foreach ($trips as $trip) {
            if (!isset($availableTrips[$trip->date])) {
                $availableTrips[$trip->date] = [
                    'date' => $trip->date,
                    'available' => 0,
                    'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
                    "price" => $trip->carListing ? $trip->carListing->price : null,
                    "capacity" => $trip->carListing ? $trip->carListing->capacity : null,
                    "capacityClass" => $trip->carListing ? $this->getCapacityClass($trip->carListing) : null,
                ];
            }

            $cancel = (int) request()->query('cancel');

            if (! (bool) $cancel) {
                $dates = [
                    '2022-12-21',
                    '2022-12-22',
                    '2022-12-23',
                ];
            } else {
                $dates = [
                    '2022-12-21',
                    '2022-12-23',
                ];
                $send = true;
            }

            if (in_array($trip->date, $dates)) {
                $availableTrips[$trip->date]['capacity'] = 0;
                $availableTrips[$trip->date]['capacityClass'] = 'red';
            } else {
                $availableTrips[$trip->date]['available'] = ! $trip->noVehicleCapacity;
            }
        }

        if ($send) {
            $accountSid = getenv("TWILIO_SID");
            $authToken = getenv("TWILIO_AUTH_TOKEN");
            $twilioNumber = getenv("TWILIO_NUMBER");

            $client = new Client($accountSid, $authToken);

            $client->messages->create('+447876133461', [
                'from' => $twilioNumber,
                'body' => 'Cancellation alert! A space for a car has become available on the 22nd December. Visit https://www.northlinkferries.co.uk to book!'
            ]);
        }

        $availableTrips = array_values($availableTrips);

        return $availableTrips;
    }

    private function getCapacityClass(TripPrice $tripPrice): string
    {
        if (!$tripPrice->available) {
            return 'red';
        }

        if ($tripPrice->capacity <= 50) {
            return 'yellow';
        }

        return 'green';
    }
}
