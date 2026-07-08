# Integración Backend ↔ Frontend

Este documento describe cómo el backend Laravel se conecta con la SPA Vue 3 en `argos.frontend`.

## Diagrama general

```
┌─────────────────────────────────────────────────────────────────┐
│                     argos.frontend (Vue 3 SPA)                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌───────────────┐  │
│  │  Pages   │  │  Argos   │  │  Pinia   │  │ @notsoweb/vue │  │
│  │ (módulos)│  │Components│  │  stores  │  │  Api, Page    │  │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └───────┬───────┘  │
│       └─────────────┴─────────────┴────────────────┘          │
│                              │                                 │
│                    Axios + Ziggy (route names)                 │
│                    Bearer token (Passport)                     │
└──────────────────────────────┼─────────────────────────────────┘
                               │ HTTPS
                               ▼
┌─────────────────────────────────────────────────────────────────┐
│                   argos.backend (Laravel 13)                   │
│  /api/*  →  Passport auth  →  Controllers  →  Models          │
│                                                                  │
│  Ziggy: GET /api/resources/routes  →  mapa de rutas nombradas   │
│  Reverb: WebSocket (opcional) para notificaciones y presencia   │
└─────────────────────────────────────────────────────────────────┘
```

## Descubrimiento de rutas (Ziggy)

Al arrancar, el frontend solicita:

| Endpoint | Ruta nombrada | Propósito |
|----------|---------------|-----------|
| `GET /api/resources/routes` | `resources.routes` | Mapa Ziggy completo → `window.Ziggy` |
| `GET /api/resources/app` | `resources.app` | Versión, nombre y metadatos de la app |

Todas las llamadas posteriores usan `route('nombre.ruta', params)` de `ziggy-js`, que resuelve la URL contra `VITE_API_URL`.

## Autenticación compartida

| Paso | Backend | Frontend |
|------|---------|----------|
| Login | `POST auth.login` → access token Passport | `defineApiToken()`, `defineUser()` |
| Peticiones | Middleware `auth:api` valida Bearer | Header `Authorization: Bearer {token}` |
| Logout | `POST auth.logout` revoca token | `logout()` de `@notsoweb/vue/Services/Page` |
| Passkeys | `PasskeyController` + Spatie | `src/services/Passkeys.js` + WebAuthn browser |
| Permisos | Spatie guard `api` | `bootPermissions()`, `hasPermission()` |

## Mapeo módulo a módulo

| Módulo backend | Rutas API (prefijo) | Módulo frontend | Rutas Vue |
|--------------|---------------------|-----------------|-----------|
| Auth | `auth.*` | `pages/Auth/` | `auth.index`, `auth.forgot-password`, `auth.reset-password` |
| Users (perfil) | `user.*` | `pages/Profile/` | `profile.show` |
| Admin Users | `admin.users.*` | `pages/Admin/Users/` | `admin.users.*` |
| Admin Roles | `admin.roles.*` | `pages/Admin/Roles/` | `admin.roles.*` |
| Admin Activities | `admin.activities.*` | `pages/Admin/Activities/` | `admin.activities.index` |
| System Notifications | `system.notifications.*` | `stores/Notifier.js` + `Profile/Notifications/` | `profile.notifications.index` |
| System Roles | `system.roles.*` | Selectores en formularios admin | — |
| Changelogs | `changelogs` | `pages/Changelogs/` | `changelogs.app`, `changelogs.core` |
| **Gym Machines** | `gym.machines.*` | `pages/Gym/Machines/` | `gym.machines.index`, `gym.machines.create`, `gym.machines.edit` |
| **Gym Exercises** | `gym.exercises.*`, `gym.exercises.note(.upsert)` | `pages/Gym/Exercises/` | `gym.exercises.index`, `gym.exercises.create`, `gym.exercises.edit` |
| **Gym Plans** | `gym.plans.*`, `gym.plans.exercises(.sync)` | `pages/Gym/Plans/` | `gym.plans.index`, `gym.plans.create`, `gym.plans.edit` |
| **Gym Registros** | `gym.registros.*`, `gym.registros.last`, `gym.registros.session`, `gym.registros.charts` | `pages/Gym/{Registros,Registro,Charts}/` | `gym.registros.index`, `gym.registro`, `gym.charts` |

## Contrato Gym Registros

El tipo efectivo del ejercicio (`exercise.type_ek ?? machine.type_ek`) define las métricas aceptadas por `POST/PUT /api/gym/registros`:

| Tipo | Métricas requeridas | Métricas prohibidas |
|------|---------------------|---------------------|
| `R` Repeticiones | `series`, `reps` | `weight`, `duration`, `distance`, `speed`, `incline` |
| `W` Fuerza | `series`, `reps`, `weight` | `duration`, `distance`, `speed`, `incline` |
| `D` Distancia | `duration` (minutos), `distance` | `series`, `reps`, `weight` |
| `T` Tiempo isométrico | `series`, `duration` (segundos) | `reps`, `weight`, `distance`, `speed`, `incline` |

Las métricas por defecto de `GET /api/gym/registros/charts` para `T` son `series` y `duration`.

Modo Registro consulta `GET /api/gym/registros/session?plan_id={id}&date=YYYY-MM-DD` al elegir plan o cambiar fecha. La respuesta `{ completed_exercise_ids: number[] }` marca como hechos solo los ejercicios con al menos un registro del usuario autenticado en esa fecha (comparando `performed_at` por día, sin hora). El prellenado del formulario sigue usando `GET /api/gym/registros/last?exercise_id={id}` y puede devolver un registro de cualquier fecha anterior.

## Tiempo real (Reverb)

| Canal backend | Evento | Consumidor frontend |
|---------------|--------|---------------------|
| `Global` | `Users\GlobalNotification` | Store `notifier` → toast |
| `Online` | Presencia | Plugin `AuthUsers.js` → `admin.users.online` |
| `App.Models.User.{id}` | Notificaciones, `Users\RoleUpdate` | Store `notifier` |
| `App.Models.Role.{id}` | `Roles\PermissionUpdate` | Recarga permisos del usuario |

Activación: `BROADCAST_CONNECTION=reverb` (backend) y `VITE_REVERB_ACTIVE=true` (frontend).

## URLs en desarrollo

| Variable | Ejemplo | Descripción |
|----------|---------|-------------|
| `APP_URL` | `http://argos.mdev.test/core` | URL pública del API |
| `APP_FRONTEND_URL` | `http://argos.mdev.test` | Origen CORS y passkeys |
| `VITE_API_URL` | `http://argos.mdev.test/core` | Base URL para Axios |
| `VITE_BASE_URL` | `http://argos.mdev.test` | Origen de la SPA |

El path `/core` suele ser un alias de reverse proxy hacia `/api` en entornos locales.

## Convención de permisos

Los permisos Spatie del backend coinciden con los guards del frontend:

```
users.index      →  beforeEnter: hasPermission('users.index')
roles.create     →  can('create') en Module.js → hasPermission('roles.create')
activities.index →  hasPermission('activities.index')
```

Los roles sembrados (`developer`, `admin`) determinan qué módulos del frontend son accesibles tras `bootPermissions()`.

## Extender la integración

Para agregar una nueva funcionalidad de dominio:

1. **Backend**: ruta en `routes/api.php` bajo `auth:api`, controlador, Form Request, y tests PHPUnit.
2. **Frontend**: página en `src/pages/`, entrada en el router, `Module.js` con `apiTo()` / `viewTo()`.
3. Ziggy expone automáticamente la nueva ruta nombrada al reiniciar el backend.
