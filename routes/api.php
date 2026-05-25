<?php

use Illuminate\Http\Request;
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
 */
Route::middleware('auth:api')->group(function() {
    // Rutas de la aplicación
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
Route::group(['middleware' => 'guest:api'], function() {
    // Rutas públicas
});
