<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\Trip;
use Inertia\Inertia;
use GuzzleHttp\Client;
use App\Services\NorthlinkService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CapacityController extends NorthlinkController
{
    public function __invoke()
    {
        dd(
            'j'
        );
        $year = (int) Route::input('year');
        $month = (int) Route::input('month');
        $routeCode = Route::input('routeCode');

        $firstDayOfMonth = date('Y-m-d', strtotime($year . '-' . $month . '-01'));

        $trip = Trip::query();
        $trip->join('trip_prices', 'trips.id', '=', 'trip_prices.trip_id');
        $trip->where('trips.date', '>=', $firstDayOfMonth);
        $trip->where('trips.date', '<=', date('Y-m-d', strtotime($firstDayOfMonth . ' + 1 month')));
        $trip->where('trips.routeCode', $routeCode);

        $trips = $trip->get();

        $capacityPerMonth = [];

        dd($trips);
        foreach ($trips as $trip) {
            $capacityPerMonth[$trip->date] = [
                'date' => $trip->date,
                'capacity' => $trip->capacity,
            ];
        }

        $capacityPerMonth = array_values($capacityPerMonth);

        return $capacityPerMonth;
    }
}