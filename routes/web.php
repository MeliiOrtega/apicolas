<?php

use App\Http\Controllers\Colecturia\ProcesarPagoLineaController;
use Illuminate\Support\Facades\Route;
use App\Jobs\ProcessPodcast;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    ProcessPodcast::dispatchAfterResponse();
    ProcessPodcast::dispatch();
    ProcessPodcast::dispatch()->onQueue('secondary');
    return response("fin"); //view('welcome');
});

// Route::post('/procesar-pago-linea', [ProcesarPagoLineaController::class, 'procesar'] );
