<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\Trip;
use App\Models\Token;
use App\Models\TripAccommodation;
use GuzzleHttp\Client;
use App\Models\TripPrice;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\BadResponseException;

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

    public function generateSinglePersonPayloadForToken(
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

    public function fetchToken(array $payload): string
    {
        $client = new Client();

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

    public function fetchTokenForSinglePerson(): string
    {
        return $this->fetchToken($this->generateSinglePersonPayloadForToken());
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

        $json = $res->getBody();
        $data = json_decode($json, true);
        $results = isset($data["res"]['result'][0]) ? $data["res"]['result'][0] : null;

        return $results;
    }

    public function getTripByRouteAndDate(string $route, string $date): ?Trip
    {
        return Trip::where('routeCode', $route)
            ->where('date', $date)
            ->first();
    }

    public function updateOrCreateTripRecords(
        array $data,
        string $date,
        string $routeCode
    ): void {
        $trip = $this->getTripByRouteAndDate($routeCode, $date);

        if ($trip && $trip->created_at->diffInMinutes(now()) < 15) {
            return;
        }

        if ($trip) {
            logger([
                "date" => $date,
                "noVehicleCapacity" => $trip->noVehicleCapacity,
            ]);

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
            $trip = Trip::create([
                'date' => $date,
                'routeCode' => $routeCode,
                'price' => $data['price'],
                'bookable' => $data['bookable'],
                'noAccommodationsAvailable' => $data['noAccommodationsAvailable'],
                'noVehicleCapacity' => $data['noVehicleCapacity'],
                'noPassengerCapacity' => $data['noPassengerCapacity'],
                'departFrom' => $this->getRouteString($data['supplier'])->departFrom,
                'returnFrom' => $this->getRouteString($data['supplier'])->returnFrom,
                'identifier' => $data['identifier'],
                'hashId' => $data['hashId'],
                'startDate' => $data['startDate'],
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
    public function updateVehicleAvailabilityStatus(
        array $data,
        string $date,
        string $routeCode
    ): void {
        $trip = $this->getTripByRouteAndDate($routeCode, $date);

        if ($trip && $trip->created_at->diffInMinutes(now()) < 5) {
            return;
        }

        if (!$trip) {
            $trip = Trip::create([
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

        if ($trip) {
            $trip->noVehicleCapacity = $data['noVehicleCapacity'];
            $trip->identifier = $data['identifier'];
            $trip->hashId = $data['hashId'];

            $trip->save();
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

    public function mockDepart(
        string $token,
        string $outboundToken,
        string $returnToken
    ): bool {
        try {
            $this->client->request(
                'POST',
                'https://www.northlinkferries.co.uk/api/book/departures',
                [
                    'headers' => [
                        'Authorization' => $token,
                    ],
                    'form_params' => [
                        'identifiers' => [
                            $outboundToken,
                            $returnToken,
                        ],
                    'http_errors' => false,
                    ]
                ],
            );

            return true;
        } catch (ServerException $e) {
            logger($e->getMessage());
            return false;
        }
    }

    public function fetchAccomodation(
        string $dateString,
        string $outboundRouteCode,
        string $returnRouteCode
    ) {
        $token = $this->fetchTokenForSinglePerson();
        $date = Carbon::parse($dateString);

        $outbound = $this->getTripByRouteAndDate($outboundRouteCode, $date);
        $return = $this->getTripByRouteAndDate($returnRouteCode, $date->addDays(2));

        if (!$outbound || !$return) {
            logger([
                "dateString" => $dateString,
                "outboundRouteCode" => $outboundRouteCode,
                "returnRouteCode" => $returnRouteCode,
            ]);
            return;
        }

        if (!isset($return->identifier)) {
            logger([
                "dateString" => $dateString,
                "outboundRouteCode" => $outboundRouteCode,
                "returnRouteCode" => $returnRouteCode,
            ]);
            return;
        }

        $success = $this->mockDepart(
            $token,
            $outbound->identifier,
            $return->identifier
        );

        if (!$success) {
            logger("Failed to mock depart");
            return;
        }

        // reset all TripAccommodations
        TripAccommodation::where('trip_id', $outbound->id)->delete();

        try {
            $res = $this->client->request(
                'POST',
                'https://www.northlinkferries.co.uk/api/accommodations/ABLE',
                [
                    'headers' => [
                        'Authorization' => $token,
                    ],
                    'http_errors' => false
                ],
            );

            $json = $res->getBody();
            $data = json_decode($json, true);
            if (!isset($data['res']['result']['cabins'])) {
                logger("No cabins found");
                return;
            }

            $cabins = $data['res']['result']['cabins'];

            foreach ($cabins as $cabin) {
                TripAccommodation::create([
                    'trip_id' => $outbound->id,
                    'amount' => $cabin['amount'],
                    'bnBIncluded' => $cabin['bnBIncluded'],
                    'capacity' => $cabin['capacity'],
                    'description' => $cabin['description'],
                    'extrasIncluded' => $cabin['extrasIncluded'],
                    'hasSeaView' => $cabin['hasSeaView'],
                    'identifier' => $cabin['identifier'],
                    'isAccessibleCabin' => $cabin['isAccessibleCabin'],
                    'price' => $cabin['price'],
                    'resourceCode' => $cabin['resourceCode'],
                    'sleeps' => $cabin['sleeps'],
                ]);
            }
        } catch (Exception $e) {
            logger([
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
        }
    }
}
