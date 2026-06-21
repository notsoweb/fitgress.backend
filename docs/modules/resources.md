# Módulo: Recursos y Metadatos

Expone información de la aplicación, el mapa de rutas Ziggy y un endpoint dinámico multi-recurso.

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Http/Controllers/ResourceController.php` | App info, rutas, recursos dinámicos |
| `app/Http/Controllers/Resources/UserResource.php` | Ejemplo de recurso (`user:test`) |
| `app/Http/Controllers/ServerController.php` | Health y versión |
| `app/Http/Controllers/ChangelogController.php` | Changelog hardcodeado |
| `app/Models/Setting.php` | Configuración clave/valor |

## Rutas API

| Método | Ruta | Nombre | Auth |
|--------|------|--------|------|
| GET | `/api/` | `status` | Pública |
| GET | `/api/resources/app` | `resources.app` | Pública |
| GET | `/api/resources/routes` | `resources.routes` | Pública |
| POST | `/api/resources/get` | `resources.get` | Auth |
| GET | `/api/version` | `version` | Auth |
| GET | `/api/changelogs` | `changelogs` | Auth |

## Conexión con el frontend

Estos endpoints son el **primer contacto** de la SPA al arrancar (`src/index.js`):

```javascript
const routes = await axios.get(apiURL('resources/routes'));
const appData = await axios.get(apiURL('resources/app'));
window.Ziggy = routes.data;
defineApp(appData.data);
```

Si fallan, la app monta `Errors/503.vue`.

| Backend | Frontend |
|---------|----------|
| `resources.routes` | `window.Ziggy` — todas las llamadas `route()` |
| `resources.app` | `defineApp()` — versión y metadatos |
| `changelogs` | `pages/Changelogs/Core.vue` |

## Relaciones

- **Integración**: sin Ziggy el frontend no puede resolver ninguna ruta API nombrada.
- **Settings**: valores de branding/config persistidos en tabla `settings`.
