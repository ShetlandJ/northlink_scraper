<?php

use App\Http\Controllers\Home\CapacityController;
use App\Http\Controllers\Home\PetCabinController;
use App\Models\Word;
use Illuminate\Support\Str;
use App\Models\WordOfTheDay;
use Illuminate\Http\Request;
use App\Services\WordService;
use App\Services\AdminService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/pet-cabins/{month}/{year}', PetCabinController::class);
Route::get('/capacity/{month}/{year}/{routeCode}', CapacityController::class);