<?php

namespace App\Services;

use Exception;
use App\Models\Trip;
use App\Models\Token;
use GuzzleHttp\Client;
use App\Models\TripPrice;
use Illuminate\Support\Facades\DB;

class NorthlinkService
{
    private const LERWICK = 'LE';
    private const ABERDEEN = 'AB';
    private const LERWICK_TO_ABERDEEN = 'LEAB';
    private const ABERDEEN_TO_LERWICK = 'ABLE';
    private const TOKEN_GENERATION_LINK = 'https://www.northlinkferries.co.uk/api/booking/create';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getToken(): ?string
    {
        $token = Token::first();

        if ($token) {
            return $token->token;
        }

        return null;
    }

    public function setToken(string $newToken): void
    {
        $token = Token::first();

        if ($token) {
            $token->token = $newToken;
            $token->save();
        } else {
            Token::create(['token' => $newToken]);
        }
    }

    public function generatePayloadForToken(
        string $outbound = self::LERWICK_TO_ABERDEEN,
        string $return = self::ABERDEEN_TO_LERWICK
    ): array {
        return [
            "departureRequests" => [
                [
                    "route" => $outbound,
                    "date" => date("Y-m-d", strtotime('tomorrow')),
                    "resources" => [[
                        "resourceCode" => "PAX",
                        "amount" => "1",
                        "resourceType" => "A",
                        "type" => "STD"
                    ]],
                    "vehicles" => [],
                ],
                [
                    "route" => $return,
                    "date" => date("Y-m-d", strtotime("+5 days")),
                    "resources" => [[
                        "resourceCode" => "PAX",
                        "amount" => "1",
                        "resourceType" => "A",
                        "type" => "STD"
                    ]],
                    "vehicles" => [],
                ],
            ]
        ];
    }

    public function fetchToken(): string
    {
        $payload = $this->generatePayloadForToken();

        $options = [
            'json' => $payload
        ];

        $postReq = $this->client->post(self::TOKEN_GENERATION_LINK, $options);

        $json = $postReq->getBody();
        $data = json_decode($json, true);

        $token = $data['token'];

        $this->setToken($token);

        return $token;
    }

    public function fetchDataByDate(string $date, string $routeCode = self::LERWICK_TO_ABERDEEN): ?array
    {
        $token = $this->getToken();

        $res = $this->client->request(
            'GET',
            sprintf(
                'https://www.northlinkferries.co.uk/api/departures/%s/prices/%s',
                $routeCode,
                $date,
            ),
            [
                'headers' => [
                    'Authorization' => $token
                ]
            ]
        );

        logger(["TOKEN!", $token]);

        $json = $res->getBody();
        $data = json_decode($json, true);
        $results = isset($data["res"]['result'][0]) ? $data["res"]['result'][0] : null;

        $this->fetchAccomodationsForDate($token);

        return $results;
    }

    public function updateOrCreateTripRecords(array $data, string $date, string $routeCode)
    {
        $trip = Trip::where('date', $date)
            ->where('routeCode', $routeCode)
            ->first();

        if ($trip && $trip->created_at->diffInMinutes(now()) < 15) {
            return;
        }

        if ($trip) {
            $trip->date = $date;
            $trip->routeCode = $routeCode;
            $trip->price = (float) $data['price'];
            $trip->bookable = $data['bookable'];
            $trip->noAccommodationsAvailable = $data['noAccommodationsAvailable'];
            $trip->noVehicleCapacity = $data['noVehicleCapacity'];
            $trip->noPassengerCapacity = $data['noPassengerCapacity'];
            $trip->departFrom = $this->getRouteString($data['supplier'])->departFrom;
            $trip->returnFrom = $this->getRouteString($data['supplier'])->returnFrom;
            $trip->startDate = $data['startDate'];
            $trip->hashId = $data['hashId'];
            $trip->identifier = $data['identifier'];

            $trip->save();
        } else {
            Trip::create([
                'date' => $date,
                'routeCode' => $routeCode,
                'price' => $data['price'],
                'bookable' => $data['bookable'],
                'noAccommodationsAvailable' => $data['noAccommodationsAvailable'],
                'noVehicleCapacity' => $data['noVehicleCapacity'],
                'noPassengerCapacity' => $data['noPassengerCapacity'],
                'departFrom' => $this->getRouteString($data['supplier'])->departFrom,
                'returnFrom' => $this->getRouteString($data['supplier'])->returnFrom,
            ]);
        }

        // delete all tripPrices
        TripPrice::where('trip_id', $trip->id)->delete();

        foreach ($data['prices'] as $price) {
            $tripPrice = new TripPrice();
            $tripPrice->trip_id = $trip->id;
            $tripPrice->resourceCode = $price['resourceCode'];
            $tripPrice->price = $price['price'];
            $tripPrice->ticketType = $price['ticketType'];
            $tripPrice->type = $price['type'];
            $tripPrice->available = $price['available'];
            $tripPrice->yieldClass = $price['yieldClass'];
            $tripPrice->capacity = $price['capacity'];
            $tripPrice->intervalValue = $price['intervalValue'];
            $tripPrice->resourceType = $price['resourceType'];

            $tripPrice->save();
        }
    }

    public function getRouteString(string $routeCode)
    {
        $routeCode = str_split($routeCode, 2);

        $first = $routeCode[0];
        $last = $routeCode[1];

        $payload = [
            "departForm" => null,
            "returnFrom" => null
        ];

        if ($first === self::LERWICK) {
            $payload['departFrom'] = 'Lerwick';
        } elseif ($first === self::ABERDEEN) {
            $payload['departFrom'] = 'Aberdeen';
        }

        if ($last === self::ABERDEEN) {
            $payload['returnFrom'] = 'Aberdeen';
        } elseif ($last === self::LERWICK) {
            $payload['returnFrom'] = 'Lerwick';
        }

        return (object) $payload;
    }

    public function fetchAccomodationsForDate($token)
    {
        $part1 = [
            'route' => 'LEAB',
            'date' => '2022-10-14',
            'resources' => [
                'amount' => 2,
                'resourceCode' => 'PAX',
                'resourceType' => 'A',
                'type' => 'STD'
            ],
            'hashId' => 'LEAB120221201900',
        ];

        $part2 = [
            'route' => 'ABLE',
            'date' => '2022-10-16',
            'resources' => [
                'amount' => 2,
                'resourceCode' => 'PAX',
                'resourceType' => 'A',
                'type' => 'STD'
            ],
            'hashId' => 'ABLE202211241700',
        ];

        $data = [
            $part1, $part2
        ];

                // create a get request with form data to https://www.northlinkferries.co.uk/api/accommodations/LEAB
        $firstToken = Trip::where('startDate', '>', '2022-10-14 00:00:00')
            ->first()
            ->identifier;

        logger(["SECOND TOKEN", $token]);

        $res =$this->client->request(
            'POST',
            'https://www.northlinkferries.co.uk/api/book/departures',
            [
                'headers' => [
                    'Authorization' => $token,
                ],
                'form_params' => [
                    'identifiers' => [
                        'Tk9STXxMRUFCfDIwMjItMTEtMjAgMTk6MDB8UEFYfFNURHxBQXxCZXN0UHJpY2VBbW91bnQ=&',
                        'Tk9STXxBQkxFfDIwMjItMTEtMjQgMTc6MDB8UEFYfFNURHxBQXxCZXN0UHJpY2VBbW91bnQ=&',
                    ]
                ]
            ],
        );

        $res = $this->client->request(
            'POST',
            'https://www.northlinkferries.co.uk/api/accommodations/LEAB',
            [
                'headers' => [
                    'Authorization' => $token,
                ],
            ],
        );

        $json = $res->getBody();
        $data = json_decode($json, true);
        dd($data);



        // [
        //     "data[0][route]"=> "LEAB",
        //     "data[0][date]" => "2022-11-20",
        //     "data[0][resources][0][amount]" => 2,
        //     "data[0][resources][0][resourceCode]" =>"PAX",
        //     "data[0][resources][0][resourceType]" => "A",
        //     "data[0][resources][0][type]" => "STD",
        //     "data[0][hashId]" => "LEAB20221120",
        //     "data[1][route]" => "ABLE",
        //     "data[1][date]" => "2022-11-24",
        //     "data[1][resources][0][amount]" => 2,
        //     "data[1][resources][0][resourceCode]" => "PAX",
        //     "data[1][resources][0][resourceType]" => "A",
        //     "data[1][resources][0][type]" => "STD",
        //     "data[1][hashId]" => "ABLE20221124",
        // ]
    }

}