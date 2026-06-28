---
name: argos-backend-development
description: Desarrolla y extiende el backend Argos (Laravel 13) siguiendo convenciones del proyecto: estilo PHP, Form Requests, Spatie permissions, ApiResponse, documentación en docs/ y tests PHPUnit. Activar al crear o modificar controladores, modelos, rutas, Actions, migraciones o módulos API.
---

# Argos Backend Development

## Antes de codificar

1. Activar skill `codebase-memory` — explorar código existente
2. Activar `laravel-best-practices` para patrones Laravel
3. Leer `docs/architecture.md` y el módulo en `docs/modules/`
4. Laravel Boost `search-docs` para APIs de paquetes

## Estilo PHP (convenciones del proyecto)

### Archivos

```php
<?php namespace App\Http\Controllers;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */
```

### Clases

- PHPDoc con descripción, `@author Moisés Cortés C. <soy@mcortes.dev>`, `@version`
- Tipos explícitos en parámetros y retorno
- Constructor property promotion cuando aplique

### Respuestas API

```php
return ApiResponse::OK->response(['user' => $user]);
return ApiResponse::UNPROCESSABLE_CONTENT->response(['email' => [__('auth.failed')]]);
```

### Autorización

- Permisos en Form Requests (`authorize()` con `can()` o middleware Spatie)
- Sin Policies — usar Spatie Permission
- Middleware `HasMiddleware` en controladores cuando corresponda

### Rutas

- Núcleo Argos: `routes/core.php` — no modificar sin necesidad
- Dominio propio: `routes/api.php` bajo `auth:api` o `guest:api`

## Estructura típica de un módulo nuevo

```
app/Http/Controllers/MiModuloController.php
app/Http/Requests/MiModulo/MiModuloStoreRequest.php
routes/api.php  (ruta nombrada)
docs/modules/mi-modulo.md
tests/Feature/MiModuloTest.php
```

## Tests obligatorios

- Feature test por endpoint o flujo HTTP
- Unit test para lógica en Actions/Supports sin HTTP
- `php artisan make:test --phpunit MiModuloTest`
- Ejecutar: `php artisan test --compact --filter=MiModulo`
- `vendor/bin/pint --dirty --format agent` tras editar PHP

## Documentación obligatoria

Actualizar siempre:

- `docs/modules/<modulo>.md` — rutas, archivos, flujo
- `docs/database.md` — si hay modelos nuevos
- `docs/integration.md` — contrato con frontend
- `docs/README.md` — índice

## Skills relacionados

- `passport-development` — OAuth2 y tokens
- `laravel-permission-development` — roles y permisos
- `codebase-memory` — exploración de código
