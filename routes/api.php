<?php

use App\Http\Controllers\Gym\MachineController;
use App\Http\Controllers\Gym\PlanController;
use App\Http\Controllers\Gym\RegistroController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del núcleo de la aplicación.
 *
 * Se recomienda que no se modifiquen estas rutas a menos que sepa lo que está haciendo.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
include 'core.php';

/**
 * Rutas de la aplicación.
 *
 * Estas rutas son de la aplicación API que desarrollarás. Siéntete libre de agregar lo que consideres necesario.
 * Procura revisar que no existan rutas que entren en conflicto con las rutas del núcleo.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
Route::middleware('auth:api')->name('gym.')->prefix('gym')->group(function () {
    Route::apiResource('machines', MachineController::class);
    Route::apiResource('plans', PlanController::class);
    Route::get('plans/{plan}/machines', [PlanController::class, 'machines'])->name('plans.machines');
    Route::put('plans/{plan}/machines', [PlanController::class, 'syncMachines'])->name('plans.machines.sync');
    Route::get('registros/charts', [RegistroController::class, 'charts'])->name('registros.charts');
    Route::apiResource('registros', RegistroController::class);
});

/**
 * Rutas públicas.
 *
 * Estas rutas son públicas y no requieren autenticación.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
Route::group(['middleware' => 'guest:api'], function () {
    // Rutas públicas
});
