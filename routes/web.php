<?php

use App\Models\Ingredient;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\Home\RecipeController;
use App\Http\Controllers\Home\CategoryController;
use App\Http\Controllers\Home\IngredientController;
use App\Http\Controllers\Home\RecipeCreateController;
use App\Http\Controllers\Home\SupplierController;
use Inertia\Inertia;

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

Route::get('/pets', function() {
    return Inertia::render('PetCabinAvailability');
})->name('pets');