<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use Inertia\Inertia;
use GuzzleHttp\Client;
use App\Services\NorthlinkService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


class HomeController extends NorthlinkController
{
    public function index()
    {
        $client = new Client();


        $fetchToken = $this->northlinkService->fetchToken();
        }

        // $payload = [
        //     "departureRequests" => [
        //         [
        //             "route" => "ABLE",
        //             "date" => date("Y-m-d"),
        //             "resources" => [[
        //                 "resourceCode" => "PAX",
        //                 "amount" => "2",
        //                 "resourceType" => "A",
        //                 "type" => "STD"
        //             ]],
        //             "vehicles" => [],
        //         ],
        //         [
        //             "route" => "LEAB",
        //             "date" => date("Y-m-d", strtotime("+5 days")),
        //             "resources" => [[
        //                 "resourceCode" => "PAX",
        //                 "amount" => "2",
        //                 "resourceType" => "A",
        //                 "type" => "STD"
        //             ]],
        //             "vehicles" => [],
        //         ],
        //     ]
        // ];

        // $options = [
        //     'json' => $payload
        //    ];

        // $postReq = $client->post('https://www.northlinkferries.co.uk/api/booking/create', $options);

        // $json = $postReq->getBody();
        // $data = json_decode($json, true);

        // dd($data);


        // $res = $client->request('GET', 'https://www.northlinkferries.co.uk/api/departures/LEAB/prices/2022-10-23', [
        //     'headers' => [
        //         'Authorization' => 'LTnh92Sl2kiozLA6TcvcSpbwcYSOSNT'
        //     ]
        // ]);

        // // get json response
        // $json = $res->getBody();
        // $data = json_decode($json, true);
        // $prices = $data["res"]['result'][0]['prices'];
        // dd(json_encode($data["res"]["result"][0]["prices"]));
        // // map prices to only show resource_code and ticket_type
        // $prices = array_map(function ($price) {
        //     return [
        //         'resource_code' => $price['resourceCode'],
        //         'ticket_type' => $price['ticketType']
        //     ];
        // }, $prices);

        // dd(json_encode($prices));

        // echo $res->getStatusCode();
        // // "200"
        // echo $res->getHeader('content-type')[0];
        // // 'application/json; charset=utf8'
        // echo $res->getBody();
        // {"type":"User"...'






        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'isLoggedIn' => Auth::check(),
        ]);
    }
}
