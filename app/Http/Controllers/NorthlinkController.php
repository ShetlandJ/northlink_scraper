<?php

namespace App\Http\Controllers;

use App\Services\NorthlinkService;
use Illuminate\Routing\Controller;

class NorthlinkController extends Controller
{
    public NorthlinkService $northlinkService;

    public function __construct(NorthlinkService $northlinkService)
    {
        $this->northlinkService = $northlinkService;
    }
}
