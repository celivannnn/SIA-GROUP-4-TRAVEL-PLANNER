<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\TravelHotelController;
use App\Http\Controllers\PhotoController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

use App\Http\Controllers\TravelFormController;

use App\Http\Controllers\AviationFlightController;


Route::get('/weather', [WeatherController::class, 'getWeather']);

Route::get('/flights/search', [FlightController::class, 'search']);

Route::get('/convert', [CurrencyController::class, 'convert']);

Route::get('/places/city/{city}', [PlaceController::class, 'getPlacesByCity']);

Route::get('/hotels/search', [TravelHotelController::class, 'searchHotels']);

Route::get('/photos', [PhotoController::class, 'getPhotos']);

Route::get('/travel-form', [TravelFormController::class, 'index']);
Route::get('/travel-form/{id}', [TravelFormController::class, 'show']);
Route::post('/travel-form', [TravelFormController::class, 'store']);
Route::put('/travel-form/{id}', [TravelFormController::class, 'update']);
Route::delete('/travel-form/{id}', [TravelFormController::class, 'destroy']);

Route::get('/find/flights', [AviationFlightController::class, 'search']);