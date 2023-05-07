<?php

namespace App\Console\Commands;

use OpenAI;
use App\Models\Trip;
use Illuminate\Console\Command;

class CheapestDealFinder extends Command
{
    // signature
    protected $signature = 'gpt {routeCode}';

    // description
    protected $description = 'Finds the cheapest deal for a given route';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $routeCode = $this->argument('routeCode');

        if (!$routeCode) {
            $this->error('Route code is required');
            return;
        }

        $yourApiKey = getenv('OPEN_API_SECRET');

        $client = OpenAI::client($yourApiKey);

        $inputRequest = sprintf("
            'I need SQL to get next date where noAccommodationsAvailable is true, for routeCode '%s'
        ", $routeCode);

        $inputRequest = 'I need SQL to get get me all the in July 2023 where noAccommodationsAvailable and noVehicleCapacity is false, for routeCode ' . $routeCode . ', and only show me results where there are cabins available';

        $cabinCodes = "NI4,NI4S,NPETI4,NI2,NPETI2,NO2,NPETO2,NO3,NPREM,NPETPR,NEXEC";
        $nonCabinCodes = "POD,POD2,POD3,NRSEAT";

        // $inputRequest = 'I need SQL to get me any trip in July 2023 '

        $prompt = sprintf("
            Respond with a syntactically correct MySQL 5.7 response (only). 
            The following keywords are columns on the trips table: date, routeCode, noAccommodationsAvailable, price, noVehicleCapacity.
            The following keywords are columns on the trip_accommodations table: resourceCode, capacity, price.
            trips (id) is linked to trip_accommodations (trip_id)
            These are cabin codes: %s
            The database table is named 'trips'.
            The request: %s
        ", $cabinCodes, $inputRequest);

        $result = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [ 
                    "role" => 'user',
                    "content" => $prompt
                ]
            ],
            'temperature' => 0.5,
        ]);

        // dump_die result and remove ALL \n newlines 
        dd(str_replace("\n", " ", $result['choices'][0]['message']['content']));
        // str_replace("\n", "", $result['choices'][0])
    // );
    }
}
