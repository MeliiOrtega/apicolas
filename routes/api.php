<?php

use App\Http\Controllers\Colecturia\ProcesarPagoLineaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/procesar-pago-linea', [ProcesarPagoLineaController::class, 'procesar']);
