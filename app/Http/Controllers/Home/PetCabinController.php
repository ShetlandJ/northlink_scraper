<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\Trip;
use Illuminate\Support\Facades\Route;

class PetCabinController extends NorthlinkController
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
        $trip->join('trip_prices', 'trips.id', '=', 'trip_prices.trip_id');
        $trip->where('trip_prices.resourceCode', 'like', '%NPET%');
        $trip->where('trips.date', '>=', $firstDayOfMonth);
        $trip->where('trips.date', '<=', date('Y-m-d', strtotime($firstDayOfMonth . ' + 1 month')));
        $trip->where('trips.routeCode', $routeCode);

        $trips = $trip->get();

        $availableTrips = [];

        foreach ($trips as $trip) {
            $availableTrips['2023-01-01'] = [
                'date' => '2023-01-01',
                'available' => 0,
                'past' => false,
            ];

            if (!isset($availableTrips[$trip->date])) {
                $availableTrips[$trip->date] = [
                    'date' => $trip->date,
                    'available' => 0,
                    'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
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
