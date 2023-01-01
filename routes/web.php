<?php

use Inertia\Inertia;
use App\Models\JobRun;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Home\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/pets', function () {
    $job = JobRun::where('job_name', 'ScrapeOnePaxData')->first();

    $payload = [
        'lastFetched' => null,
        'currentlyRunning' => $job ? $job->currentlyRunning : false,
    ];

    return Inertia::render('PetCabinAvailability', [
        'jobStatus' => $payload,
    ]);
})->name('pets');

Route::get('/cars', function () {
    $job = JobRun::where('job_name', 'ScrapeCarDataOnePax')->first();

    $payload = [
        'lastFetched' => null,
        'currentlyRunning' => $job ? $job->currentlyRunning : false,
    ];

    return Inertia::render('CarAvailabilityPage', [
        'jobStatus' => $payload,
    ]);
})->name('cars');

// Route::get('/text-demo', function () {
//     $job = JobRun::where('job_name', 'ScrapeCarDataOnePax')->first();

//     $payload = [
//         'lastFetched' => $job->finished_at->diffForHumans(),
//         'currentlyRunning' => $job->currentlyRunning,
//     ];

//     return Inertia::render('TextDemoPage', [
//         'jobStatus' => $payload,
//     ]);
// })->name('text-demo');

Route::get('/cars', function () {
    $job = JobRun::where('job_name', 'ScrapeCarDataOnePax')->first();

    $payload = [
        'lastFetched' => null,
        'currentlyRunning' => $job ? $job->currentlyRunning : null,
    ];

    return Inertia::render('CarAvailabilityPage', [
        'jobStatus' => $payload,
    ]);
})->name('cars');

Route::get('/rooms', function () {
    $job = JobRun::where('job_name', 'GetTripAccommodation')->first();

    $payload = [
        'lastFetched' => null,
        'currentlyRunning' => $job ? $job->currentlyRunning : null,
    ];

    return Inertia::render('RoomsAvailabilityPage', [
        'jobStatus' => $payload,
    ]);
})->name('accommodation');

Route::get('/capacity', function () {
    return Inertia::render('CapacityPage');
})->name('capacity');

Route::get('/find-a-trip', function () {
    return Inertia::render('FindATripPage');
})->name('find-a-trip');

Route::get('/about', function () {
    return Inertia::render('AboutPage');
})->name('about');
