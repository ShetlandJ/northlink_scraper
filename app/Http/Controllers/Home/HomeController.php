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
        // $client = new Client();

        $trip = Trip::query();
        $trip->join('trip_prices', 'trips.id', '=', 'trip_prices.trip_id');

        // where trip_prices.resourceCode like NPET
        $trip->where('trip_prices.resourceCode', 'like', '%NPET%');
        $trip->where('trip_prices.available', true);

        // dq($trip);
        dd($trip->first());



        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'isLoggedIn' => Auth::check(),
        ]);
    }
}
