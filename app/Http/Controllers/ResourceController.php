<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Models\Setting;
use Illuminate\Http\Request;
use Notsoweb\ApiResponse\Enums\ApiResponse;
use Tighten\Ziggy\Ziggy;

/**
 * Recursos de la aplicación
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class ResourceController extends Controller
{
    /**
     * Obtener cualquier recurso
     * 
     * Procesa la matriz de recursos solicitados y retorna una respuesta con los datos de cada recurso.
     */
    public function get(Request $request)
    {
        $resources = $request->json();
        $response = [];

        foreach ($resources as $resource => $data) {
            $exploded = explode(':', $resource);

            try {
                $class = "App\Http\Controllers\Resources\\{$this->toPascalCase($exploded[0])}Resource";
                $method = $this->toCamelCase($exploded[1]);

                $response[$resource] = (new $class)->{$method}($data);
            } catch (\Exception $e) {
                $response[$resource] = false;
            }
        }

        return ApiResponse::OK->onSuccess($response);
    }

    /**
     * Información de la aplicación
     */
    public function app()
    {
        return ApiResponse::OK->axios([
            'logo' => Setting::value('app.logo'),
            'favicon' => Setting::value('app.favicon'),
            'version' => config('app.version'),
        ]);
    }

    /**
     * Rutas de la aplicación
     */
    public function routes()
    {
        return ApiResponse::OK->axios((new Ziggy('api'))->toArray());
    }

    /**
     * Transforma un string de formato kebab-case o snake_case a PascalCase
     * 
     * @param string $string String a transformar
     * @return string String en formato PascalCase
     */
    private function toPascalCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    /**
     * Transforma un string de formato kebab-case o snake_case a camelCase
     * 
     * @param string $string String a transformar
     * @return string String en formato camelCase
     */
    private function toCamelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
    }
}
