<?php

namespace App\Services;

use Exception;
use App\Models\Trip;
use App\Models\Token;
use GuzzleHttp\Client;
use App\Models\TripPrice;
use DateTime;
use Illuminate\Support\Facades\DB;

class ConfigService
{
    private const LERWICK_TO_ABERDEEN = 'LEAB';
    private const ABERDEEN_TO_LERWICK = 'ABLE';
    // private const TOKEN_GENERATION_LINK = 'https://www.northlinkferries.co.uk/api/booking/create';

    public function makePax(
        string $amount = "1",
        string $resourceCode = 'PAX',
        string $resourceType = 'A',
        string $type = "STD"
    ): array {
        return [
            "resourceCode" => $resourceCode,
            "amount" => (string) $amount,
            "resourceType" => $resourceType,
            "type" => $type
        ];
    }

    public function makeVehicle(
        $resourceCode = "CAR",
        $length = ""
    ): array {
        return [
            "resourceCode" => $resourceCode,
            "length" => $length,
            "licenseNumber" => "",
            "rental" => true,
        ];
    }

    public function makeRoute(
        string $route = self::LERWICK_TO_ABERDEEN,
        string $date,
        string $paxAmount = "1",
        ?string $vehicleCode = null,
        string $length = ''
    ): array {
        return [
            "route" => $route,
            "date" => $date,
            "resources" => [$this->makePax($paxAmount)],
            "vehicles" => $vehicleCode ? [$this->makeVehicle($vehicleCode, $length)] : [],
        ];
    }

    public function formatRequest(
        string $outbound = self::LERWICK_TO_ABERDEEN,
        string $return = self::ABERDEEN_TO_LERWICK,
        string $outboundDate,
        string $returnDate,
        string $paxAmount = "1",
        ?string $vehicleCode = null,
        string $length = ''
    ): array {
        return [
            "departureRequests" => [
                $this->makeRoute($outbound, $outboundDate, $paxAmount, $vehicleCode, $length),
                $this->makeRoute($return, $returnDate, $paxAmount, $vehicleCode, $length)
            ]
        ];
    }
}