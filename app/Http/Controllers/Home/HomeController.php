<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\Trip;
use Inertia\Inertia;
use GuzzleHttp\Client;
use App\Services\NorthlinkService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


class HomeController extends NorthlinkController
{
    public function index()
    {
        $trip = Trip::query();
        $trip->join('trip_prices', 'trips.id', '=', 'trip_prices.trip_id');
        $trip->where('trip_prices.resourceCode', 'like', '%NPET%');
        $trip->where('trips.date', '>=', now()->format('Y-m-d'));
        $trip->where('trips.date', '<=', now()->addDays(30)->format('Y-m-d'));

        $trips = $trip->get();

        $availableTrips = [];

        foreach ($trips as $trip) {
            // if array not keyed by date, key it
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

        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'isLoggedIn' => Auth::check(),
            'petCabins' => $availableTrips,
        ]);
    }
}
