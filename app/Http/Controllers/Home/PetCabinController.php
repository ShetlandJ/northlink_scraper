<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\Trip;
use Inertia\Inertia;
use GuzzleHttp\Client;
use App\Services\NorthlinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


class PetCabinController extends NorthlinkController
{
    public function __invoke()
    {
        $year = (int) Route::input('year');
        $month = (int) Route::input('month');

        $trip = Trip::query();
        $trip->join('trip_prices', 'trips.id', '=', 'trip_prices.trip_id');
        $trip->where('trip_prices.resourceCode', 'like', '%NPET%');
        $trip->where('trips.date', '>=', now()->format('Y-m-d'));
        $trip->where('trips.date', '<=', now()->addDays(30)->format('Y-m-d'));

        $trips = $trip->get();

        $availableTrips = [];

        foreach ($trips as $trip) {
            if (!isset($availableTrips[$trip->date])) {
                $availableTrips[$trip->date] = [
                    'date' => $trip->date,
                    'available' => 0,
                ];
            }

            if ($trip->available) {
                $availableTrips[$trip->date]['available'] = 1;
            }
        }

        $availableTrips = array_values($availableTrips);

        // get first day of month
        $firstDayOfMonth = date('Y-m-d', strtotime($year . '-' . $month . '-01'));

        return [
            'LEAB' => $this->getAvailableTrips('LEAB', $firstDayOfMonth),
            'ABLE' => $this->getAvailableTrips('ABLE', $firstDayOfMonth),
        ];
    }

    private function getAvailableTrips(string $routeCode, string $firstDayOfMonth)
    {
        $trip = Trip::query();
        $trip->join('trip_prices', 'trips.id', '=', 'trip_prices.trip_id');
        $trip->where('trip_prices.resourceCode', 'like', '%NPET%');
        $trip->where('trips.date', '>=', $firstDayOfMonth);
        $trip->where('trips.date', '<=', date('Y-m-d', strtotime($firstDayOfMonth . ' + 1 month')));
        $trip->where('trips.routeCode', $routeCode);

        $trips = $trip->get();

        $availableTrips = [];

        foreach ($trips as $trip) {
            if (!isset($availableTrips[$trip->date])) {
                $availableTrips[$trip->date] = [
                    'date' => $trip->date,
                    'available' => 0,
                ];
            }

            if ($trip->available) {
                $availableTrips[$trip->date]['available'] = 1;
            }
        }

        $availableTrips = array_values($availableTrips);

        return $availableTrips;
    }
}
