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
        $roomTypes = ["NPETI4", "NPETO2"];

        $trip = Trip::query();
        $trip->select(
            'trips.id',
            'trips.date',
            'trips.price as tripPrice',
            'trip_accommodations.resourceCode as roomType',
            'trip_accommodations.capacity as roomCapacity',
            'trip_accommodations.price',
            'trip_accommodations.description',
        );
        $trip->join('trip_accommodations', 'trips.id', '=', 'trip_accommodations.trip_id');
        $trip->where('trips.date', '>=', $firstDayOfMonth);
        $trip->where('trips.date', '<=', date('Y-m-d', strtotime($firstDayOfMonth . ' + 1 month')));
        $trip->where('trips.routeCode', $routeCode);

        $trips = $trip->get();

        $availableTrips = [];

        foreach ($trips as $trip) {
            if (
                isset($availableTrips[$trip->date]) 
                && !in_array($trip->roomType, $roomTypes)
                && $availableTrips[$trip->date]['available'] === true
            ) {
                continue;
            }

            $availableTrips[$trip->date] = [
                'date' => $trip->date,
                'available' => false,
                'price' => (int) $trip->price,
                'trip_price' => (int) $trip->tripPrice,
                'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
                'capacity' => $trip->roomCapacity,
            ];

            if (in_array($trip->roomType, $roomTypes)) {
                $availableTrips[$trip->date] = [
                    'date' => $trip->date,
                    'available' => (bool) $trip->roomCapacity,
                    'capacity' => $trip->roomCapacity,
                    'price' => (int) $trip->price,
                    'trip_price' => (int) $trip->tripPrice,
                    'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
                ];
            }
        }

        $availableTrips = array_values($availableTrips);

        return $availableTrips;
    }
}
