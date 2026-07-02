# Base de Datos

PostgreSQL por defecto. Esquema gestionado con 14 migraciones en `database/migrations/`.

## Modelos Eloquent

| Modelo | Tabla | Descripción |
|--------|-------|-------------|
| `User` | `users` | Usuario con soft deletes |
| `Role` | `roles` | Extiende Spatie Role |
| `Permission` | `permissions` | Permiso Spatie con `permission_type_id` |
| `PermissionType` | `permission_types` | Agrupación de permisos |
| `LogEvent` | `log_events` | Auditoría polimórfica |
| `Notification` | `notifications` | Notificaciones custom (no Laravel default) |
| `PasswordResetToken` | `password_reset_tokens` | Tokens UUID de reset |
| `Setting` | `settings` | Config clave/valor |

## Diagrama de relaciones

```
User
├── belongsToMany Role (model_has_roles)
├── belongsToMany Permission (vía roles, Spatie)
├── hasMany LogEvent (history — acciones del usuario)
├── morphMany LogEvent (events — sobre el usuario)
├── hasMany PasswordResetToken
├── hasMany Passkey (Spatie)
└── hasMany Notification

Role
├── belongsToMany Permission (role_has_permissions)
└── belongsToMany User

PermissionType
└── hasMany Permission

LogEvent
├── belongsTo User (quien realizó la acción)
└── morphTo reportable (User, Role, …)
```

## Módulo Gimnasio

```
User
└── hasMany Registro (historial de entrenamiento)

Machine
├── belongsToMany Plan (gym_plan_machines, pivot position)
Machine
├── hasMany MachineProperty (name, value, unit, position)
├── belongsToMany Plan (gym_plan_machines, pivot position)
└── hasMany Registro

Plan
├── belongsToMany Machine (gym_plan_machines, pivot position)
└── hasMany Registro

Registro
├── belongsTo User
├── belongsTo Machine
└── belongsTo Plan (nullable)

MachineProperty
└── belongsTo Machine
```

- `gym_machines`: id, name, description, code (unique), type_ek (enum T/W).
- `gym_machine_properties`: id, machine_id, name, value (nullable), unit (nullable), position. Índice en `(machine_id, position)`.
- `gym_plans`: id, name, description.
- `gym_plan_machines`: plan_id, machine_id, position, unique(plan_id, machine_id).
- `gym_registros`: id, user_id, machine_id, plan_id (nullable), series, reps, weight (nullable), performed_at. Índices en `(user_id, performed_at)` y `machine_id`.

## Tablas del sistema

| Tabla | Migración | Uso |
|-------|-----------|-----|
| `users`, `sessions` | `0001_01_01_000000` | Usuarios y sesiones |
| `log_events` | `0001_01_01_000001` | Auditoría |
| `cache`, `cache_locks` | `0001_01_01_000002` | Cache database |
| `jobs`, `failed_jobs` | `0001_01_01_000003` | Cola database |
| `permission_*`, `model_has_*`, `role_has_*` | `2026_05_17_020952` | Spatie Permission |
| `oauth_*` | `2026_05_17_021034`–`021038` | Laravel Passport |
| `agent_conversations`, `agent_conversation_messages` | `2026_05_17_021935` | Laravel AI (futuro) |
| `notifications` | `2026_05_17_024231` | Notificaciones in-app |
| `settings` | `2026_05_17_024320` | Configuración app |
| `passkeys` | `2026_06_12_155113` | WebAuthn |
| `gym_machines`, `gym_plans`, `gym_plan_machines`, `gym_registros`, `gym_machine_properties` | `2026_07_02_000001`–`000005` | Módulo Gimnasio |

## Seeders

| Seeder | Contenido |
|--------|-----------|
| `RoleSeeder` | Roles `developer`, `admin` y permisos |
| `UserSeeder` | `dev@`, `admin@`, `demo@` + dominio de `MAIL_DOMAIN` |
| `DatabaseSeeder` | Orquesta seeders |

Ejecutar entorno dev: `composer run db:dev` (migrate + seed + cliente Passport personal).

## Factories

Ubicadas en `database/factories/` para tests. Usar en PHPUnit con `User::factory()`.

## Tests

PHPUnit usa SQLite en memoria (`phpunit.xml`): `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`.
