<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\FlightPrice;
use App\Models\JobRun;
use App\Models\Trip;
use Inertia\Inertia;
use GuzzleHttp\Client;
use App\Services\NorthlinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class FlightPriceController extends NorthlinkController
{
    public function __invoke()
    {
        $departureAirport = Route::input('departureAirport');
        $arrivalAirport = Route::input('arrivalAirport');

        $year = (int) Route::input('year');
        $month = (int) Route::input('month');

        $firstDayOfMonth = date('Y-m-d', strtotime($year . '-' . $month . '-01'));

        $output[$departureAirport] = $this->getAvailableTrips($firstDayOfMonth, $departureAirport, $arrivalAirport);

        return $output;
    }

    private function getAvailableTrips(
        string $firstDayOfMonth,
        string $departureAirportCode,
        string $arrivalAirportCode
    ) {
        $flightQuery = FlightPrice::query();
        $flightQuery->select('*');
        $flightQuery->where('flight_prices.departure_date', '>=', $firstDayOfMonth);
        $flightQuery->where('flight_prices.departure_date', '<=', date('Y-m-d', strtotime($firstDayOfMonth . ' + 1 month')));
        $flightQuery->where('flight_prices.departure_airport', $departureAirportCode);
        $flightQuery->where('flight_prices.arrival_airport', $arrivalAirportCode);

        $flights = $flightQuery->get();


        $flightData = [];

        foreach ($flights as $flight) {
            $flightData[$flight->departure_date->format('Y-m-d')] = [
                'date' => $flight->departure_date->format('Y-m-d'),
                'available' => true,
                'price' => (int) $flight->price,
                'past' => strtotime($flight->departure_date) < strtotime(date('Y-m-d')),
            ];
        }

        $flightData = array_values($flightData);

        return $flightData;
    }
}
