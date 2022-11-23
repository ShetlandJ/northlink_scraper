<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\NorthlinkController;
use App\Models\Trip;
use Inertia\Inertia;
use GuzzleHttp\Client;
use App\Services\NorthlinkService;
use App\Services\SearchService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class FindATripController extends NorthlinkController
{
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function __invoke()
    {
        return $this->searchService->searchTrips();
    }
}