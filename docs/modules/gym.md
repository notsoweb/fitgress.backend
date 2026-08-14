# Módulo: Gimnasio (Gym)

Gestión de máquinas (equipo), ejercicios, planes de entrenamiento y registros de progreso por usuario.

## Dominio

- **Máquina** (`gym_machines`): catálogo de equipo del gimnasio. Campos `name`, `description`, `code` (único) y `type_ek` (tipo nativo). El `code` de inventario vive aquí, no en el ejercicio.
- **Ejercicio** (`gym_exercises`): unidad entrenable. `machine_id` (nullable), `name`, `description` (nullable) y `type_ek` (nullable). Una máquina puede tener varios ejercicios. El **tipo efectivo** = `exercise.type_ek ?? machine.type_ek`.
  - **Propiedades** (`gym_exercise_properties`): atributos variables por ejercicio (altura del asiento, distancia al pecho, número de eje, etc.). `name`, `value` (nullable), `unit` (nullable), `position`. Se sincronizan desde `store`/`update` del ejercicio.
  - **Notas** (`gym_exercise_notes`): nota persistente por **usuario y ejercicio** (única por `user_id` + `exercise_id`).
- **Plan** (`gym_plans` + pivot `gym_plan_exercises` con `position`): conjunto ordenado de ejercicios para una rutina.
- **Registro** (`gym_registros`): una entrada por sesión/ejercicio del usuario autenticado con métricas dependientes del tipo y `performed_at`.

Multiusuario: cada usuario ve solo sus propios registros y notas. Máquinas, ejercicios y planes son compartidos y gestionados por admin/developer.

## Tipos de ejercicio (`MachineTypeEk`, string)

| Valor | Tipo | Campos de registro |
|-------|------|--------------------|
| `R` | Repeticiones | `series`, `reps` |
| `W` | Fuerza | `series`, `reps`, `weight` |
| `D` | Distancia | `duration` (minutos), `distance` (requeridos); `speed`, `incline` (opcionales) |
| `T` | Tiempo (isométrico) | `series`, `duration` (segundos) |

La validación de registros aplica según el **tipo efectivo** del ejercicio. Los campos que no corresponden al tipo son rechazados si vienen presentes.

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Emums/MachineTypeEk.php` | Enum `REPS`/`WEIGHT`/`DISTANCE`/`TIME` (string) |
| `app/Models/Machine.php` | Equipo (`hasMany` ejercicios) |
| `app/Models/Exercise.php` | Ejercicio (`effectiveType()`, append `effective_type`) |
| `app/Models/ExerciseProperty.php`, `ExerciseNote.php` | Propiedades y notas del ejercicio |
| `app/Models/Plan.php`, `Registro.php` | Planes y registros (referencian `exercise_id`) |
| `app/Http/Controllers/Gym/MachineController.php` | CRUD equipo (`machines.*`) |
| `app/Http/Controllers/Gym/ExerciseController.php` | CRUD ejercicios + propiedades (`exercises.*`) |
| `app/Http/Controllers/Resources/ExerciseResource.php` | Catálogo `exercise:all` (todos los ejercicios, sin paginar) |
| `app/Http/Controllers/Gym/ExerciseNoteController.php` | Nota por usuario (sin permiso Spatie) |
| `app/Http/Controllers/Gym/PlanController.php` | CRUD planes + sync ejercicios (`plans.*`) |
| `app/Http/Controllers/Gym/RegistroController.php` | CRUD registros + `last` + `charts` (scoped a `Auth::user()`) |
| `app/Http/Controllers/Gym/DashboardController.php` | Resumen para el dashboard (conteos, ejercicio top y calendario de días/planes) |
| `app/Http/Requests/Gym/*` | Validación; `ValidatesRegistroByType` centraliza reglas por tipo |
| `database/seeders/MachineSeeder.php` | Máquinas y ejercicios de ejemplo |

## Rutas API

Todas requieren `auth:api`. Prefijo `/api/gym`.

| Método | Ruta | Nombre | Permiso | Descripción |
|--------|------|--------|---------|-------------|
| GET | `/gym/dashboard` | `gym.dashboard` | — | Resumen: catálogo, días, ejercicio más realizado y calendario `{ date, plans[] }` |
| GET | `/gym/machines` | `gym.machines.index` | `machines.index` | Listado paginado |
| POST | `/gym/machines` | `gym.machines.store` | `machines.create` | Crear |
| GET | `/gym/machines/{machine}` | `gym.machines.show` | `machines.index` | Ver |
| PUT/PATCH | `/gym/machines/{machine}` | `gym.machines.update` | `machines.edit` | Editar |
| DELETE | `/gym/machines/{machine}` | `gym.machines.destroy` | `machines.destroy` | Eliminar |
| GET | `/gym/exercises` | `gym.exercises.index` | `exercises.index` | Listado paginado (con `machine`, `properties`, `effective_type`) |
| POST | `/gym/exercises` | `gym.exercises.store` | `exercises.create` | Crear (`{name, description?, machine_id?, type_ek?, properties[]}`) |
| GET | `/gym/exercises/{exercise}` | `gym.exercises.show` | `exercises.index` | Ver (con `machine` + `properties`) |
| PUT/PATCH | `/gym/exercises/{exercise}` | `gym.exercises.update` | `exercises.edit` | Editar |
| DELETE | `/gym/exercises/{exercise}` | `gym.exercises.destroy` | `exercises.destroy` | Eliminar |
| GET | `/gym/exercises/{exercise}/note` | `gym.exercises.note` | — | Nota del usuario `{ note: string\|null }` |
| PUT | `/gym/exercises/{exercise}/note` | `gym.exercises.note.upsert` | — | Upsert nota `{ note }` → `{ note }` (vacío elimina) |
| GET | `/gym/plans` | `gym.plans.index` | `plans.index` | Listado paginado (con `exercises`) |
| POST | `/gym/plans` | `gym.plans.store` | `plans.create` | Crear |
| GET | `/gym/plans/{plan}` | `gym.plans.show` | `plans.index` | Ver (con ejercicios + máquina + propiedades) |
| PUT/PATCH | `/gym/plans/{plan}` | `gym.plans.update` | `plans.edit` | Editar nombre/descripción |
| DELETE | `/gym/plans/{plan}` | `gym.plans.destroy` | `plans.destroy` | Eliminar |
| GET | `/gym/plans/{plan}/exercises` | `gym.plans.exercises` | `plans.index` | Ejercicios ordenados |
| PUT | `/gym/plans/{plan}/exercises` | `gym.plans.exercises.sync` | `plans.edit` | Sincronizar ejercicios + posición |
| GET | `/gym/registros/last` | `gym.registros.last` | — | Último registro + nota `{ model, note }` por `exercise_id` |
| GET | `/gym/registros/session` | `gym.registros.session` | — | Ejercicios completados del usuario para `plan_id` + `date` (`Y-m-d`) |
| GET | `/gym/registros/charts` | `gym.registros.charts` | — | Serie por `exercise_id` y `metric`; métricas por defecto según tipo |
| GET | `/gym/registros` | `gym.registros.index` | — | Listado del usuario (filtros `exercise_id`, `plan_id`, `from`, `to`) |
| POST | `/gym/registros` | `gym.registros.store` | — | Crear (validación por tipo efectivo) |
| GET | `/gym/registros/{registro}` | `gym.registros.show` | — | Ver (autorización por `user_id`) |
| PUT/PATCH | `/gym/registros/{registro}` | `gym.registros.update` | — | Editar (autorización por `user_id`) |
| DELETE | `/gym/registros/{registro}` | `gym.registros.destroy` | — | Eliminar (autorización por `user_id`) |

> `registros/last`, `registros/session` y `registros/charts` se declaran **antes** de `apiResource('registros')` para evitar colisión con `registros/{registro}`.

## Permisos Spatie

Definidos en `RoleSeeder` bajo el tipo `Gimnasio`:

- `machines.{index,create,edit,destroy}`
- `exercises.{index,create,edit,destroy}`
- `plans.{index,create,edit,destroy}`

Los registros y las notas NO requieren permiso Spatie: cualquier usuario autenticado gestiona **sus** registros y **sus** notas (scoped por `user_id`).

## `effective_type`

El modelo `Exercise` expone el atributo calculado `effective_type` (append) = `type_ek ?? machine?->type_ek` (como cadena `R`/`W`/`D`/`T` o `null`). El frontend lo consume directamente; además se serializan `type_ek` y `machine`.

## Modelo de datos

```
Machine 1─N Exercise
                  ├── 1─N ExerciseProperty (name, value, unit, position)
                  ├── 1─N ExerciseNote (user_id, note)  [único por user+exercise]
                  ├── N─M Plan (pivot gym_plan_exercises.position)
                  └── 1─N Registro
User 1─N Registro N─1 Exercise
User 1─N ExerciseNote
Plan 1─N Registro (nullable)
```

## Flujo "Modo Registro"

1. `GET /api/gym/plans` → usuario elige un plan y una fecha (`Y-m-d`).
2. `GET /api/gym/plans/{plan}/exercises` → ejercicios en orden de ejecución.
3. `GET /api/gym/registros/session?plan_id=…&date=YYYY-MM-DD` → `{ completed_exercise_ids: number[] }` con ejercicios que ya tienen ≥1 registro del usuario en esa fecha. La comparación usa solo la parte de fecha de `performed_at`.
4. Al seleccionar un ejercicio: `GET /api/gym/registros/last?exercise_id=…` → prellena el último registro histórico y muestra la nota; no se limita al día elegido.
5. `POST /api/gym/registros` con los campos del tipo efectivo (`{exercise_id, plan_id, ...métricas, performed_at}`). Para `D`, `duration` se expresa en minutos; para `T`, en segundos. En Modo Registro el frontend envía `performed_at` alineado a la fecha elegida.
6. `GET /api/gym/registros?exercise_id=…&from=…&to=…` → historial.
7. `GET /api/gym/registros/charts?exercise_id=…&metric=weight` → serie para gráfica de progreso.

## Tests

`tests/Feature/ExerciseTest.php`, `ExerciseNoteTest.php`, `ExerciseResourceTest.php`, `MachineTest.php`, `PlanTest.php`, `RegistroTest.php`, `DashboardTest.php` cubren CRUD, autorización por permiso, scoped multiusuario, `effective_type`, validación por tipo (R/W/D/T), `last`, `session` por fecha, notas, el catálogo `exercise:all` y el resumen del dashboard.

```
php artisan test --compact --filter="(ExerciseTest|ExerciseNoteTest|ExerciseResourceTest|MachineTest|PlanTest|RegistroTest|DashboardTest)"
```
