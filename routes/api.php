<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\TravelHotelController;
use App\Http\Controllers\PhotoController;

Route::get('/weather', [WeatherController::class, 'getWeather']);

Route::get('/flights/search', [FlightController::class, 'search']);

Route::get('/convert', [CurrencyController::class, 'convert']);

Route::get('/places/city/{city}', [PlaceController::class, 'getPlacesByCity']);

Route::get('/hotels/search', [TravelHotelController::class, 'searchHotels']);

Route::get('/photos', [PhotoController::class, 'getPhotos']);
