<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\JobRun;
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

        $year = (int) Route::input('year');
        $month = (int) Route::input('month');
        $routeCode = Route::input('routeCode');

        $firstDayOfMonth = date('Y-m-d', strtotime($year . '-' . $month . '-01'));

        $output[$routeCode] = $this->getAvailableTrips($routeCode, $firstDayOfMonth, $roomType);

        return $output;
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

        $roomAvailability = [];

        foreach ($trips as $trip) {
            if (isset($roomAvailability[$trip->date]) && $trip->roomType !== $roomType) {
                continue;
            }

            $roomAvailability[$trip->date] = [
                'date' => $trip->date,
                'available' => false,
                'price' => (int) $trip->price,
                'trip_price' => (int) $trip->tripPrice,
                'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
                'capacity' => $trip->roomCapacity,
            ];

            if ($trip->roomType === $roomType) {
                $roomAvailability[$trip->date] = [
                    'date' => $trip->date,
                    'available' => (bool) $trip->roomCapacity,
                    'capacity' => $trip->roomCapacity,
                    'price' => (int) $trip->price,
                    'trip_price' => (int) $trip->tripPrice,
                    'past' => strtotime($trip->date) < strtotime(date('Y-m-d')),
                ];
            }
        }

        $roomAvailability = array_values($roomAvailability);

        return $roomAvailability;
    }
}
