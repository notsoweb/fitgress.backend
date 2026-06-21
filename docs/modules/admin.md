# Módulo: Administración

CRUD de usuarios, roles y permisos, más visor de actividades (auditoría). Protegido por permisos Spatie.

## Submódulos

### Admin — Usuarios

| Archivo | Rol |
|---------|-----|
| `app/Http/Controllers/Admin/UserController.php` | CRUD, asignación de roles, reset password |
| `app/Http/Requests/Admin/UserStoreRequest.php` | Permiso `users.create` |
| `app/Http/Requests/Admin/UserUpdateRequest.php` | Permiso `users.edit` |
| `app/Observers/UserObserver.php` | Auditoría automática |

| Método | Ruta | Nombre |
|--------|------|--------|
| GET | `/api/admin/users` | `admin.users.index` |
| POST | `/api/admin/users` | `admin.users.store` |
| GET | `/api/admin/users/{user}` | `admin.users.show` |
| PUT/PATCH | `/api/admin/users/{user}` | `admin.users.update` |
| DELETE | `/api/admin/users/{user}` | `admin.users.destroy` |
| PUT | `/api/admin/users/{user}/password` | `admin.users.password` |
| GET | `/api/admin/users/{user}/permissions` | `admin.users.permissions` |
| GET | `/api/admin/users/{user}/roles` | `admin.users.roles` |
| PUT | `/api/admin/users/{user}/roles` | `admin.users.roles` (sync) |

Al sincronizar roles emite evento `App\Events\Users\RoleUpdate` → canal `App.Models.User.{id}`.

### Admin — Roles

| Archivo | Rol |
|---------|-----|
| `app/Http/Controllers/Admin/RoleController.php` | CRUD + sync permisos (middleware por acción) |
| `app/Http/Controllers/Admin/PermissionTypeController.php` | Árbol tipos → permisos |
| `app/Observers/RoleObserver.php` | Auditoría automática |
| `app/Models/Role.php` | Extiende Spatie Role |
| `app/Models/PermissionType.php` | Agrupa permisos |
| `database/seeders/RoleSeeder.php` | Roles y permisos iniciales |

| Método | Ruta | Nombre |
|--------|------|--------|
| GET | `/api/admin/roles` | `admin.roles.index` |
| POST | `/api/admin/roles` | `admin.roles.store` |
| GET | `/api/admin/roles/{role}` | `admin.roles.show` |
| PUT/PATCH | `/api/admin/roles/{role}` | `admin.roles.update` |
| DELETE | `/api/admin/roles/{role}` | `admin.roles.destroy` |
| GET | `/api/admin/roles/{role}/permissions` | `admin.roles.permissions` |
| PUT | `/api/admin/roles/{role}/permissions` | `admin.roles.permissions.update` |
| GET | `/api/permission-types/all-with-permissions` | `permission-types.all-with-permissions` |

Middleware en `RoleController`: `roles.index`, `roles.create`, `roles.edit`, `roles.destroy`.

Al actualizar permisos emite `App\Events\Roles\PermissionUpdate` → canal `App.Models.Role.{id}`.

### Admin — Actividades

| Archivo | Rol |
|---------|-----|
| `app/Http/Controllers/Admin/ActivityController.php` | Listado de log de auditoría |
| `app/Models/LogEvent.php` | Registro polimórfico de actividad |

| Método | Ruta | Nombre |
|--------|------|--------|
| GET | `/api/admin/activities` | `admin.activities.index` |

Requiere permiso `activities.index`.

## Roles sembrados

| ID | Nombre | Permisos |
|----|--------|----------|
| 1 | `developer` | Todos |
| 2 | `admin` | Users CRUD, settings, roles CRUD, activities.index |

## Permisos (ejemplos)

`users.index`, `users.create`, `users.edit`, `users.destroy`, `users.settings`, `users.online`, `roles.index`, `roles.create`, `roles.edit`, `roles.destroy`, `activities.index`

## Conexión con el frontend

| Backend | Frontend |
|---------|----------|
| `admin.users.*` | `pages/Admin/Users/` — `Module.js`, Index, Create, Edit, Settings, Online |
| `admin.roles.*` | `pages/Admin/Roles/` — Index, Create, Edit, modal Permissions |
| `admin.activities.*` | `pages/Admin/Activities/` — Index, modal Event |
| `permission-types.all-with-permissions` | Modal de permisos en roles |
| `system.roles.index` | Selectores de rol en formularios de usuario |

Patrón frontend: `apiTo('index')` → `route('admin.users.index')`, guards con `hasPermission()`.

## Relaciones

- **Users**: admin gestiona usuarios; observers escriben en `log_events`.
- **System**: `system.roles` y `system.permissions` alimentan selectores del frontend.
- **Notifications/Broadcast**: cambios de rol/permiso notifican en tiempo real a usuarios conectados.
