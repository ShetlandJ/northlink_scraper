<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\Trip;
use App\Models\TripPrice;
use Inertia\Inertia;
use GuzzleHttp\Client;
use App\Services\NorthlinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CarAvailabilityController extends NorthlinkController
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

            $availableTrips[$trip->date]['available'] = ! $trip->noVehicleCapacity;
        }

        $availableTrips = array_values($availableTrips);
        dd($availableTrips);

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
