# Arquitectura del Backend

## Propósito

El backend sirve como API para una SPA Vue 3. La mayor parte del código proviene del scaffold **Argos** (auth, RBAC, notificaciones, admin). La lógica de dominio propio se agrega en controladores bajo `app/Http/Controllers/` y rutas en `routes/api.php`.

## Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Framework | Laravel 13 |
| Autenticación API | Laravel Passport 13 (OAuth2, personal access tokens) |
| Permisos | Spatie Laravel Permission 7 |
| Passkeys | Spatie Laravel Passkeys 1.8 |
| IA (opcional) | Laravel AI 0.6 (`laravel/ai`) — configurado, sin agentes implementados aún |
| Tiempo real | Laravel Reverb 1 |
| Backup | Spatie Laravel Backup 10 |
| Boilerplate | `notsoweb/laravel-core` |
| Exportación de rutas | Tightenco Ziggy 2 |
| Cola | Driver `database` |
| Cache / sesiones | Driver `database` |
| Tests | PHPUnit 12 |

## Estructura de directorios

```
app/
├── Actions/Passkeys/          # Acciones WebAuthn
├── Console/Commands/          # Comandos Artisan (notify, mail test)
├── Events/                    # Eventos broadcast
├── Http/
│   ├── Controllers/           # Controladores API
│   │   ├── Admin/             # Usuarios, roles, actividades
│   │   ├── System/            # Referencias y notificaciones
│   │   └── Resources/         # Cargadores dinámicos de recursos
│   ├── Requests/              # Form requests con validación y permisos
│   └── Traits/                # HasProfilePhoto
├── Models/                    # Modelos Eloquent
├── Notifications/             # Notificaciones (email, broadcast)
├── Observers/                 # Auditoría automática (User, Role)
└── Supports/                  # QuerySupport

routes/
├── api.php                    # Rutas de dominio propio + include core.php
├── core.php                   # Rutas del núcleo Argos
├── channels.php               # Autorización de canales broadcast
└── web.php

database/
├── migrations/                # 14 migraciones
├── seeders/                   # RoleSeeder, UserSeeder, etc.
└── factories/

config/                        # 18 archivos de configuración
tests/Feature, tests/Unit
```

> **Extensión IA:** al implementar agentes, crear `app/Ai/Agents/` (ver [modules/ai-agents.md](./modules/ai-agents.md)).

## Punto de entrada HTTP

`bootstrap/app.php` registra:

- Prefijo `/api` para todas las rutas API
- Middleware de sesión en el grupo API (requerido para ceremonias WebAuthn)
- Aliases de middleware Spatie: `role`, `permission`, `role_or_permission`
- Health check en `/up`

## Organización de rutas

| Archivo | Contenido |
|---------|-----------|
| `routes/core.php` | Auth, usuarios, admin, sistema, recursos, changelogs — **núcleo Argos** |
| `routes/api.php` | Grupos `auth:api` y `guest:api` para rutas de dominio propio |

Las rutas de dominio se agregan en `api.php` para no interferir con el núcleo.

## Patrones de diseño

- **Sin capa Service/Jobs dedicada**: la lógica vive en controladores y el paquete `notsoweb/laravel-core`.
- **Sin Policies**: permisos verificados vía Spatie en Form Requests y middleware `HasMiddleware` en controladores.
- **Respuestas API**: enum `Notsoweb\ApiResponse\Enums\ApiResponse` (`OK`, etc.).
- **Auditoría**: Observers en `User` y `Role` escriben en `log_events`.
- **Broadcast-only events**: no hay listeners; los eventos solo emiten por Reverb.
- **Comentarios**: bloque `@copyright` en archivos, PHPDoc en clases con `@author` y `@version`, comentarios breves en métodos públicos.

## Flujo de una petición autenticada

```
Cliente SPA
    │  Authorization: Bearer {token}
    ▼
CORS middleware
    ▼
auth:api (Passport)
    ▼
permission / role middleware (si aplica)
    ▼
Controller → Model
    ▼
ApiResponse JSON
```

## Servicios en segundo plano

Definidos en `composer.json` scripts y `routes/console.php`:

| Servicio | Comando |
|----------|---------|
| Queue worker | `php artisan queue:listen` |
| Scheduler | `php artisan schedule:work` |
| Reverb | `php artisan reverb:start` |
| Backup | `backup:clean` (01:00), `backup:run` (01:30) diario |

Orquestación local: `composer run services:start` (PM2).

## Documentación viva

Tras cambios en modelos, rutas o módulos, actualizar `docs/` y el índice en [README.md](./README.md). Usar [codebase-memory.md](./codebase-memory.md) para explorar el código antes de modificarlo.
