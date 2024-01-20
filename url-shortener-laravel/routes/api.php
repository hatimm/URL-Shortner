<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlShortenerController;


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

Route::get('/shortened-urls', [UrlShortenerController::class, 'index']);
Route::post('/shorten', [UrlShortenerController::class, 'shorten'])->name('shorten');
Route::get('/{shortUrl}', [UrlShortenerController::class, 'redirect'])->name('redirect');


