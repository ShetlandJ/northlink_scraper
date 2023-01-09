<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Home\CapacityController;
use App\Http\Controllers\Home\PetCabinController;
use App\Http\Controllers\Home\TextDemoController;
use App\Http\Controllers\Home\FindATripController;
use App\Http\Controllers\Home\FlightPriceController;
use App\Http\Controllers\Home\CarAvailabilityController;
use App\Http\Controllers\Home\AccommodationAvailabilityController;

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

Route::get('/pet-cabins/{month}/{year}/{routeCode}', PetCabinController::class);
Route::get('/cars/{month}/{year}/{routeCode}', CarAvailabilityController::class);
Route::get('/text-demo/{month}/{year}/{routeCode}', TextDemoController::class);
Route::get('/accommodation/{month}/{year}/{routeCode}', AccommodationAvailabilityController::class);
Route::get('/flights/{month}/{year}/{departureAirport}/{arrivalAirport}', FlightPriceController::class);
Route::get('/capacity/{month}/{year}/{routeCode}', CapacityController::class);
Route::post('/find-a-trip', FindATripController::class);
