<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\TrelloController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\CardController;

Route::get('/', function () {
    return view('welcome');
});

Route::apiResource('trello', TrelloController::class);
Route::apiResource('list', ListController::class);
Route::apiResource('card', CardController::class);
Route::get('/download',[TrelloController::class, 'download']);