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

class AccommodationAvailabilityController extends NorthlinkController
{
    public function __invoke()
    {
        // get request content
        $request = request()->all();
        $roomType = $request['roomType'];
        // dd($roomType);

        $year = (int) Route::input('year');
        $month = (int) Route::input('month');

        $firstDayOfMonth = date('Y-m-d', strtotime($year . '-' . $month . '-01'));

        return [
            'LEAB' => $this->getAvailableTrips('LEAB', $firstDayOfMonth, $roomType),
            'ABLE' => $this->getAvailableTrips('ABLE', $firstDayOfMonth, $roomType),
        ];
    }

    private function getAvailableTrips(
        string $routeCode,
        string $firstDayOfMonth,
        string $roomType
    ) {
        $trip = Trip::query();
        $trip->select(
            'trips.id',
            'trips.date',
            'trip_accommodations.resourceCode',
            'trip_accommodations.capacity',
            'trip_accommodations.price',
            'trip_accommodations.description'
        );
        $trip->join('trip_accommodations', 'trips.id', '=', 'trip_accommodations.trip_id');
        $trip->where('trip_accommodations.resourceCode', $roomType);
        $trip->where('trips.date', '>=', $firstDayOfMonth);
        $trip->where('trips.date', '<=', date('Y-m-d', strtotime($firstDayOfMonth . ' + 1 month')));
        $trip->where('trips.routeCode', $routeCode);

        $trips = $trip->get();

        $availableTrips = [];

        foreach ($trips as $trip) {
            if (!isset($availableTrips[$trip->date])) {
                $availableTrips[$trip->date] = [
                    'date' => $trip->date,
                    'available' => $trip->capacity,
                    'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
                    'price' => $trip->price,
                ];
            }
        }

        $availableTrips = array_values($availableTrips);

        return $availableTrips;
    }
}
