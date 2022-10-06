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
        dd($this->getAvailabilityForTrips());

        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'isLoggedIn' => Auth::check(),
        ]);
    }

    public function getAvailabilityForTrips()
    {
        $trips = Trip::all();

        $availability = [];

        foreach ($trips as $trip) {
            $availability[$trip->id] = $trip->prices->where('resourceCode', 'PAX')->first()->capacity;
        }

        return $availability;
    }
}
