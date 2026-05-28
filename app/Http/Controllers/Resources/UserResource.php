<?php namespace App\Http\Controllers\Resources;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;

/**
 * Recurso de usuario
 */
class UserResource extends Controller
{
    /**
     * Prueba de recurso
     */
    public function test()
    {
        return [
            "test" => "OK"
        ];
    }
}