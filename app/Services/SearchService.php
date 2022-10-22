<?php

namespace App\Services;

use Exception;
use App\Models\Trip;
use App\Models\Token;
use GuzzleHttp\Client;
use App\Models\TripPrice;
use DateTime;
use Illuminate\Support\Facades\DB;

class SearchService
{
    private const LERWICK_TO_ABERDEEN = 'LEAB';
    private const ABERDEEN_TO_LERWICK = 'ABLE';

    public function searchTrips()
    {
        $request = request()->input();

        $routeCode = $request['outbound'];
        $dates = $request['dates'];
        $minimumDayGap = $request['minimumDayGap'];
        $paxAmount = $request['passengers'];
        $hasVehicle = $request['car'];
        $hasPet = $request['pet'];

        // dd($routeCode, $minimumDayGap, $paxAmount, $hasVehicle, $hasPet);

        // get all trips by routeCode where the date is at least the day of the start date
        $trips = Trip::where('routeCode', $routeCode)
            ->where('date', '>=', $dates['start'])
            ->where('date', '<=', $dates['end'])

        if ($hasVehicle) {
            $trips->where('noVehicleCapacity', true);
        }

        $get = $trips->get();
        dd($get);
    }


}