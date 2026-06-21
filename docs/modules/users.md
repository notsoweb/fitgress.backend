# Módulo: Perfil de Usuario

Gestión del perfil del usuario autenticado: datos personales, contraseña, foto, roles y permisos propios.

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Http/Controllers/UserController.php` | CRUD del perfil propio |
| `app/Http/Traits/HasProfilePhoto.php` | Almacenamiento de foto de perfil |
| `app/Http/Requests/User/*` | Validación |
| `app/Models/User.php` | Modelo principal |

## Rutas API

Todas requieren `auth:api`.

| Método | Ruta | Nombre | Descripción |
|--------|------|--------|-------------|
| GET | `/api/user` | `user.show` | Datos del usuario autenticado |
| PUT | `/api/user` | `user.update` | Actualiza nombre, email, etc. |
| DELETE | `/api/user` | `user.destroy` | Elimina cuenta (soft delete) |
| PUT | `/api/user/password` | `user.password` | Cambia contraseña |
| POST | `/api/user/password-confirm` | `user.password-confirm` | Confirma contraseña para acciones sensibles |
| GET | `/api/user/permissions` | `user.permissions` | Permisos efectivos del usuario |
| GET | `/api/user/roles` | `user.roles` | Roles asignados |
| DELETE | `/api/user/photo` | `user.photo` | Elimina foto de perfil |

## Modelo User

Traits y relaciones principales:

- `HasApiTokens` (Passport)
- `HasRoles` (Spatie)
- `HasPasskeys` / `InteractsWithPasskeys`
- `SoftDeletes`
- `hasMany` → `LogEvent` (historial de acciones)
- `morphMany` → `LogEvent` (eventos sobre el usuario)
- `hasMany` → `PasswordResetToken`
- `Notifiable` → notificaciones Laravel

## Conexión con el frontend

| Backend | Frontend |
|---------|----------|
| `user.*` | `pages/Profile/Show.vue` y partials en `Profile/Partials/` |
| `user.password-confirm` | `components/Argos/Form/ConfirmsPassword.vue` |
| `user.passkeys.*` | `Profile/Partials/PasskeysForm.vue` |

Estado del usuario: `$page.user` vía `pagePlugin` de `@notsoweb/vue`. Tras actualizar perfil se llama `reloadUser()`.

## Relaciones con otros módulos

- **Auth**: el usuario se obtiene del token Passport tras login.
- **Admin**: `Admin\UserController` gestiona otros usuarios; protege usuarios primarios (developer/admin).
- **Notifications**: el usuario recibe notificaciones en canal `App.Models.User.{id}`.
