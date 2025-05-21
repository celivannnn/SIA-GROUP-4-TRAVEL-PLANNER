<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Your other web routes here if needed
// Route::get('/some-other-route', function () { ... });

// Catch-all route for frontend SPA
Route::get('/{any}', function () {
    return File::get(public_path('dist/index.html'));
})->where('any', '^(?!api|dist|storage|favicon.ico).*$');
