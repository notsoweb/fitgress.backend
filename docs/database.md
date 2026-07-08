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
├── hasMany Registro (historial de entrenamiento)
└── hasMany ExerciseNote

Machine
└── hasMany Exercise

Exercise
├── belongsTo Machine (nullable)
├── hasMany ExerciseProperty (name, value, unit, position)
├── hasMany ExerciseNote (user_id, note) [único por user+exercise]
├── belongsToMany Plan (gym_plan_exercises, pivot position)
└── hasMany Registro

Plan
├── belongsToMany Exercise (gym_plan_exercises, pivot position)
└── hasMany Registro

Registro
├── belongsTo User
├── belongsTo Exercise
└── belongsTo Plan (nullable)

ExerciseProperty
└── belongsTo Exercise

ExerciseNote
├── belongsTo User
└── belongsTo Exercise
```

- `gym_machines`: id, name, description, code (unique), type_ek (string R/W/D/T).
- `gym_exercises`: id, machine_id (nullable, nullOnDelete), name, description (nullable), type_ek (string, nullable). Índices en `machine_id` y `name`.
- `gym_exercise_properties`: id, exercise_id, name, value (nullable), unit (nullable), position. Índice en `(exercise_id, position)`.
- `gym_exercise_notes`: id, user_id, exercise_id, note (text). Único en `(user_id, exercise_id)`.
- `gym_plans`: id, name, description.
- `gym_plan_exercises`: plan_id, exercise_id, position, unique(plan_id, exercise_id).
- `gym_registros`: id, user_id, exercise_id, plan_id (nullable), series (nullable), reps (nullable), weight (nullable), duration (nullable; minutos para `D`, segundos para `T`), distance (decimal 8,3, km), speed (decimal 6,2, km/h), incline (decimal 5,2, %), performed_at. Índices en `(user_id, performed_at)` y `exercise_id`.

El **tipo efectivo** de un ejercicio es `exercise.type_ek ?? machine.type_ek`; determina qué métricas del registro se validan.

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
| `gym_machines`, `gym_plans`, `gym_exercises`, `gym_plan_exercises`, `gym_registros`, `gym_exercise_properties`, `gym_exercise_notes` | `2026_07_02_000001`–`000007` | Módulo Gimnasio |

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
