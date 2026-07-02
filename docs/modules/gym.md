# Módulo: Gimnasio (Gym)

Gestión de máquinas, planes de entrenamiento y registros de progreso por usuario.

## Dominio

- **Máquina** (`gym_machines`): catálogo de máquinas del gimnasio. `type_ek` distingue máquinas de `WEIGHT` (peso) y `TIME` (tiempo). El campo `weight` del registro se valida según el tipo de la máquina.
  - **Propiedades** (`gym_machine_properties`): atributos variables por máquina (altura del asiento, distancia al pecho, número de eje, etc.). Cada propiedad tiene `name`, `value` y `unit` (opcional). Se sincronizan desde el mismo endpoint `store`/`update` de la máquina.
- **Plan** (`gym_plans` + pivot `gym_plan_machines` con `position`): conjunto ordenado de máquinas para una rutina.
- **Registro** (`gym_registros`): una entrada por sesión/máquina del usuario autenticado con `series`, `reps`, `weight` (nullable) y `performed_at`.

Multiusuario: cada usuario ve solo sus propios registros. Máquinas y planes son compartidos y gestionados por admin/developer.

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Emums/MachineTypeEk.php` | Enum `TIME`/`WEIGHT` |
| `app/Models/Machine.php`, `MachineProperty.php`, `Plan.php`, `Registro.php` | Modelos Eloquent |
| `app/Http/Controllers/Gym/MachineController.php` | CRUD máquinas (permisos `machines.*`) |
| `app/Http/Controllers/Gym/PlanController.php` | CRUD planes + sync máquinas (`plans.*`) |
| `app/Http/Controllers/Gym/RegistroController.php` | CRUD registros + `charts` (scoped a `Auth::user()`) |
| `app/Http/Requests/Gym/*` | Validación con autorización vía `hasPermissionTo` |
| `database/seeders/MachineSeeder.php` | Máquinas de ejemplo |

## Rutas API

Todas requieren `auth:api`. Prefijo `/api/gym`.

| Método | Ruta | Nombre | Permiso | Descripción |
|--------|------|--------|---------|-------------|
| GET | `/gym/machines` | `gym.machines.index` | `machines.index` | Listado paginado |
| POST | `/gym/machines` | `gym.machines.store` | `machines.create` | Crear |
| GET | `/gym/machines/{machine}` | `gym.machines.show` | `machines.index` | Ver |
| PUT/PATCH | `/gym/machines/{machine}` | `gym.machines.update` | `machines.edit` | Editar |
| DELETE | `/gym/machines/{machine}` | `gym.machines.destroy` | `machines.destroy` | Eliminar |
| GET | `/gym/plans` | `gym.plans.index` | `plans.index` | Listado paginado (con `machines`) |
| POST | `/gym/plans` | `gym.plans.store` | `plans.create` | Crear |
| GET | `/gym/plans/{plan}` | `gym.plans.show` | `plans.index` | Ver (con máquinas) |
| PUT/PATCH | `/gym/plans/{plan}` | `gym.plans.update` | `plans.edit` | Editar nombre/descripción |
| DELETE | `/gym/plans/{plan}` | `gym.plans.destroy` | `plans.destroy` | Eliminar |
| GET | `/gym/plans/{plan}/machines` | `gym.plans.machines` | `plans.index` | Máquinas ordenadas |
| PUT | `/gym/plans/{plan}/machines` | `gym.plans.machines.sync` | `plans.edit` | Sincronizar máquinas + posición |
| GET | `/gym/registros` | `gym.registros.index` | — | Listado del usuario (filtros `machine_id`, `plan_id`, `from`, `to`) |
| POST | `/gym/registros` | `gym.registros.store` | — | Crear (omite permiso; `weight` obligatorio si máquina es WEIGHT) |
| GET | `/gym/registros/charts` | `gym.registros.charts` | — | Serie `[{performed_at, value}]` por `machine_id` y `metric` (`weight|reps|series`) |
| GET | `/gym/registros/{registro}` | `gym.registros.show` | — | Ver (autorización por `user_id`) |
| PUT/PATCH | `/gym/registros/{registro}` | `gym.registros.update` | — | Editar (autorización por `user_id`) |
| DELETE | `/gym/registros/{registro}` | `gym.registros.destroy` | — | Eliminar (autorización por `user_id`) |

## Permisos Spatie

Definidos en `RoleSeeder` bajo el tipo `Gimnasio`:

- `machines.{index,create,edit,destroy}`
- `plans.{index,create,edit,destroy}`

Los registros NO requieren permiso Spatie: cualquier usuario autenticado puede crear/editar/eliminar **sus** registros.

## Modelo de datos

```
User 1─N Registro N─1 Machine
                          │
                          ├── 1─N MachineProperty (name, value, unit, position)
                          │
                          └── N─M ── Plan (pivot gym_plan_machines.position)
                                    │
                                    └── 1─N Registro (nullable)
```

### Propiedades de máquina

En lugar de un campo fijo de altura, cada máquina puede tener propiedades arbitrarias que describen su configuración física:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `name` | string | Nombre de la propiedad (ej. "Altura del asiento", "Distancia al pecho", "Número de eje") |
| `value` | string (nullable) | Valor (ej. "3", "150", "45°") |
| `unit` | string (nullable) | Unidad opcional (ej. "cm", "nivel", "°", "%") |
| `position` | int | Orden de visualización |

Se envían como array `properties[]` en `POST /api/gym/machines` y `PUT /api/gym/machines/{machine}`. El controlador elimina las existentes y reemplaza por las nuevas. Los endpoints `index` y `show` incluyen las propiedades eager-loaded.

## Flujo "Modo Registro"

1. `GET /api/gym/plans` → usuario elige un plan.
2. `GET /api/gym/plans/{plan}/machines` → máquinas en orden de ejecución.
3. Por cada máquina: `POST /api/gym/registros` con `{machine_id, plan_id, series, reps, weight, performed_at}`.
4. `GET /api/gym/registros?machine_id=…&from=…&to=…` → historial.
5. `GET /api/gym/registros/charts?machine_id=…&metric=weight` → serie para gráfica de progreso.

## Tests

`tests/Feature/MachineTest.php`, `PlanTest.php`, `RegistroTest.php` cubren CRUD, autorización por permiso, scoped multiusuario y validación condicional de `weight`.

```
php artisan test --compact --filter="(MachineTest|PlanTest|RegistroTest)"
```