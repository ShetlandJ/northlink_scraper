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

class CarAvailabillityController extends NorthlinkController
{
    public function __invoke()
    {
        $year = (int) Route::input('year');
        $month = (int) Route::input('month');

        $firstDayOfMonth = date('Y-m-d', strtotime($year . '-' . $month . '-01'));

        return [
            'LEAB' => $this->getAvailableTrips('LEAB', $firstDayOfMonth),
            'ABLE' => $this->getAvailableTrips('ABLE', $firstDayOfMonth),
        ];
    }

    private function getAvailableTrips(string $routeCode, string $firstDayOfMonth)
    {
        $trip = Trip::query();
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
                    'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
                ];
            }

            $availableTrips[$trip->date]['available'] = ! $trip->noVehicleCapacity;
        }

        $availableTrips = array_values($availableTrips);

        return $availableTrips;
    }
}
