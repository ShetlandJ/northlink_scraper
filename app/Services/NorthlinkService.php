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
                    "date" => date("Y-m-d"),
                    "resources" => [[
                        "resourceCode" => "PAX",
                        "amount" => "2",
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
                        "amount" => "2",
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

        $client = new Client();

        $options = [
            'json' => $payload
        ];

        $postReq = $client->post(self::TOKEN_GENERATION_LINK, $options);

        $json = $postReq->getBody();
        $data = json_decode($json, true);

        $token = $data['token'];

        $this->setToken($token);

        return $token;
    }

    public function fetchDataByDate(string $date, string $routeCode = self::LERWICK_TO_ABERDEEN): ?array
    {
        $token = $this->getToken();

        $client = new Client();

        $res = $client->request(
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

        $json = $res->getBody();
        $data = json_decode($json, true);
        $prices = isset($data["res"]['result'][0]) ? $data["res"]['result'][0] : null;

        return $prices;
    }

    public function updateOrCreateTripRecords(array $data, string $date, string $routeCode)
    {
        $trip = Trip::where('date', $date)
            ->where('routeCode', $routeCode)
            ->first();

        if ($trip && $trip->created_at->diffInMinutes(now()) < 15) {
            return;
        }

        $trip = Trip::firstOrCreate([
            'date' => $date,
            'price' => $data['price'],
            'bookable' => $data['bookable'],
            'noAccommodationsAvailable' => $data['noAccommodationsAvailable'],
            'noVehicleCapacity' => $data['noVehicleCapacity'],
            'noPassengerCapacity' => $data['noPassengerCapacity'],
            'routeCode' => $data['supplier'],
            'departFrom' => $this->getRouteString($data['supplier'])->departFrom,
            'returnFrom' => $this->getRouteString($data['supplier'])->returnFrom,
        ]);

        foreach ($data['prices'] as $index => $price) {
            TripPrice::firstOrCreate([
                "trip_id" => $trip->id,
                "resourceCode" => $price['resourceCode'],
                "price" => $price['price'],
                "ticketType" => $price['ticketType'],
                "type" => $price['type'],
                "available" => $price['available'],
                "yieldClass" => $price['yieldClass'],
                "capacity" => $price['capacity'],
                "intervalValue" => $price['intervalValue'],
                "resourceType" => $price['resourceType'],
            ]);
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
}