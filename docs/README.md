# Argos Backend — Documentación

API REST construida con **Laravel 13** sobre el boilerplate **Argos** (`notsoweb/laravel-core`). Expone autenticación OAuth2, administración de usuarios/roles, notificaciones en tiempo real y puntos de extensión para dominio propio en `routes/api.php`.

## Índice

| Documento | Descripción |
|-----------|-------------|
| [Arquitectura](./architecture.md) | Stack, estructura de carpetas y flujo general |
| [Configuración](./configuration.md) | Variables de entorno y archivos de config |
| [Base de datos](./database.md) | Modelos, relaciones y migraciones |
| [Integración](./integration.md) | Contrato con el frontend Vue |
| [Codebase Memory MCP](./codebase-memory.md) | Búsqueda de código con el grafo indexado |

### Módulos

| Módulo | Documento |
|--------|-----------|
| Autenticación (Passport + Passkeys) | [modules/auth.md](./modules/auth.md) |
| Perfil de usuario | [modules/users.md](./modules/users.md) |
| Administración (usuarios, roles, actividades) | [modules/admin.md](./modules/admin.md) |
| Sistema (referencias y notificaciones) | [modules/system.md](./modules/system.md) |
| Recursos y metadatos | [modules/resources.md](./modules/resources.md) |
| Notificaciones y broadcasting | [modules/notifications.md](./modules/notifications.md) |
| Agentes de IA (`laravel/ai`) | [modules/ai-agents.md](./modules/ai-agents.md) |
| Gimnasio (máquinas, ejercicios, planes, registros, notas) | [modules/gym.md](./modules/gym.md) |

## Información del proyecto

| Campo | Valor |
|-------|-------|
| Versión | `0.9.11` (ver `config/app.php`) |
| PHP | 8.5 |
| Base de datos por defecto | PostgreSQL |
| Idioma / zona horaria | `es` / `America/Mexico_City` |
| Prefijo API | `/api` |
| Rutas del núcleo | `routes/core.php` (no modificar sin necesidad) |
| Rutas de dominio | `routes/api.php` |

## Inicio rápido

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run db:dev   # seeders + cliente Passport personal
php artisan serve
```

Variables críticas: `APP_FRONTEND_URL`, `CORS_ALLOWED_ORIGINS`. Para IA opcional: `GEMINI_API_KEY` (ver [modules/ai-agents.md](./modules/ai-agents.md)).

## Mapa de dependencias entre módulos

```
notsoweb/laravel-core (ApiResponse, traits)
        │
        ├── Auth (Passport + Passkeys)
        │       └── Users (perfil)
        │               └── Admin (usuarios, roles)
        │                       ├── LogEvent (auditoría)
        │                       └── Broadcast Events
        │
        ├── System (referencias, notificaciones)
        └── Resources (Ziggy, metadatos app)
```

## Extender el dominio

Las rutas de negocio propio van en `routes/api.php` dentro de `auth:api`. Documentar cada módulo nuevo en `docs/modules/` y actualizar [integration.md](./integration.md).
