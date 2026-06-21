# Módulo: Sistema

Endpoints de solo lectura para datos de referencia (permisos, roles, tipos) y bandeja de notificaciones del usuario autenticado.

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Http/Controllers/System/PermissionController.php` | Lista todos los permisos |
| `app/Http/Controllers/System/PermissionTypeController.php` | Tipos de permiso |
| `app/Http/Controllers/System/RoleController.php` | Lista de roles |
| `app/Http/Controllers/System/NotificationController.php` | CRUD de notificaciones in-app |

## Rutas API

Todas requieren `auth:api`.

| Método | Ruta | Nombre | Descripción |
|--------|------|--------|-------------|
| GET | `/api/system/permissions` | `system.permissions` | Todos los permisos |
| GET | `/api/system/permission-types` | `system.permission-types` | Tipos de permiso |
| GET | `/api/system/roles` | `system.roles.index` | Lista de roles |
| GET | `/api/system/notifications/all` | `system.notifications.all` | Historial de notificaciones |
| GET | `/api/system/notifications/all-unread` | `system.notifications.all-unread` | No leídas |
| POST | `/api/system/notifications/read` | `system.notifications.read` | Marcar como leídas |
| POST | `/api/system/notifications/close` | `system.notifications.close` | Cerrar notificaciones |
| DELETE | `/api/system/notifications/destroy` | `system.notifications.destroy` | Eliminar notificaciones |

## Conexión con el frontend

| Backend | Frontend |
|---------|----------|
| `system.notifications.*` | `stores/Notifier.js`, sidebar de notificaciones, `Profile/Notifications/Index.vue` |
| `system.roles.index` | Selectores en `Admin/Users/Form.vue`, `Roles.vue` |

El store `notifier` carga `all-unread` al iniciar y se actualiza vía Echo en canal `App.Models.User.{id}`.

## Relaciones

- **Admin**: los permisos y roles listados aquí son los mismos gestionados en admin.
- **Notifications**: el modelo `Notification` custom persiste notificaciones; `UserNotification` las encola con broadcast.
