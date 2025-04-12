<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MarcacionController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('jwt.verify')->group(function () {
    Route::post('/marcaciones', [MarcacionController::class, 'store']);
    Route::get('/marcaciones/{empleado_id}', [MarcacionController::class, 'show']);
    Route::post('/reporte', [ReporteController::class, 'generarReporte']);
});
