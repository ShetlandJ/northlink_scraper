<?php

namespace App\Services;

use App\Models\Token;
use GuzzleHttp\Client;

class NorthlinkService
{
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
}