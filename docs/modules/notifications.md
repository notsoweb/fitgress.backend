# Módulo: Notificaciones y Broadcasting

Notificaciones in-app (base de datos + broadcast) y eventos en tiempo real vía Laravel Reverb.

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Notifications/UserNotification.php` | Notificación encolada (DB + broadcast) |
| `app/Events/Users/GlobalNotification.php` | Broadcast canal `Global` |
| `app/Events/Users/RoleUpdate.php` | Broadcast a usuario cuando cambian sus roles |
| `app/Events/Roles/PermissionUpdate.php` | Broadcast a rol cuando cambian permisos |
| `routes/channels.php` | Autorización de canales privados/presencia |
| `app/Console/Commands/NotifyGlobalCommand.php` | `notify:global` |
| `app/Console/Commands/NotifyTestCommand.php` | Prueba de notificaciones |

## Canales broadcast

| Canal | Tipo | Uso |
|-------|------|-----|
| `App.Models.User.{id}` | Privado | Notificaciones personales, `RoleUpdate` |
| `App.Models.Role.{id}` | Privado | `PermissionUpdate` |
| `Online` | Presencia | Usuarios conectados |
| `Global` | Público | Avisos globales (toast) |

## Eventos

| Evento | Disparado desde | Frontend |
|--------|-----------------|----------|
| `Users\GlobalNotification` | `NotifyGlobalCommand` | Toast + refresh en store `notifier` |
| `Users\RoleUpdate` | `Admin\UserController@updateRoles` | Recarga roles del usuario |
| `Roles\PermissionUpdate` | `Admin\RoleController@updatePermissions` | Recarga permisos |

No hay listeners registrados; los eventos son solo para broadcast.

## Conexión con el frontend

| Componente | Rol |
|------------|-----|
| `src/services/Broadcast.js` | Configura Echo/Reverb con Bearer token |
| `src/stores/Notifier.js` | Suscripciones y estado de notificaciones |
| `src/stores/NotificationSidebar.js` | Sidebar derecho (persistido en localStorage) |
| `src/plugins/AuthUsers.js` | Lista reactiva de usuarios online (canal `Online`) |

Activación: `BROADCAST_CONNECTION=reverb` + `VITE_REVERB_ACTIVE=true`.

Auth de canales privados: `POST {host}/{path}/broadcasting/auth` con token Passport.

## Relaciones

- **System**: `NotificationController` expone CRUD HTTP de notificaciones.
- **Admin**: cambios de roles/permisos disparan eventos broadcast.
- **Queue**: `UserNotification` implementa `ShouldQueue`; requiere worker con `QUEUE_CONNECTION=database`.
