# Configuración del Backend

## Variables de entorno principales

Copiar desde `.env.example`:

### Aplicación

| Variable | Ejemplo | Descripción |
|----------|---------|-------------|
| `APP_NAME` | Licia | Nombre de la app |
| `APP_URL` | `http://argos.mdev.test/core` | URL del API |
| `APP_FRONTEND_URL` | `http://argos.mdev.test` | SPA — CORS y passkeys |
| `APP_LOCALE` | `es` | Idioma |
| `APP_TIMEZONE` | `America/Mexico_City` | Zona horaria |
| `APP_PAGINATION` | `25` | Tamaño de página en listados |
| `CORS_ALLOWED_ORIGINS` | Origen del frontend | CORS con credenciales |

### Base de datos

| Variable | Descripción |
|----------|-------------|
| `DB_CONNECTION` | `pgsql` (producción/dev) |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Conexión PostgreSQL |

### Cola, cache y sesiones

| Variable | Valor típico |
|----------|--------------|
| `QUEUE_CONNECTION` | `database` |
| `CACHE_STORE` | `database` |
| `SESSION_DRIVER` | `database` |

### Broadcasting (Reverb)

| Variable | Descripción |
|----------|-------------|
| `BROADCAST_CONNECTION` | `reverb` |
| `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET` | Credenciales Reverb |
| `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` | Conexión WebSocket |

### Correo

| Variable | Descripción |
|----------|-------------|
| `MAIL_*` | SMTP |
| `MAIL_DOMAIN` | Dominio para emails de usuarios sembrados |
| `MAIL_BACKUP_TO` | Destino de notificaciones de backup |

### IA (LICIA)

| Variable | Descripción |
|----------|-------------|
| `GEMINI_API_KEY` | **Requerida** para análisis de documentos |
| `OPENAI_API_KEY` | Opcional, no usada por agentes actuales |

### Passport

| Variable | Descripción |
|----------|-------------|
| `PASSPORT_PRIVATE_KEY` | Opcional; default `storage/app/keys/oauth-private.key` |
| `PASSPORT_PUBLIC_KEY` | Opcional; default `storage/app/keys/oauth-public.key` |

## Archivos de configuración relevantes

| Archivo | Contenido |
|---------|-----------|
| `config/app.php` | Versión, paginación, `frontend.url`, `profile_disk` |
| `config/auth.php` | Guard `api` con driver Passport |
| `config/ai.php` | Proveedores de IA |
| `config/passport.php` | OAuth2 |
| `config/permission.php` | Spatie — guard `api` |
| `config/passkeys.php` | WebAuthn — relying party desde `app.frontend.url` |
| `config/reverb.php` | Servidor WebSocket |
| `config/cors.php` | `api/*`, credentials enabled |
| `config/backup.php` | Spatie backup |

## Service providers

`bootstrap/providers.php`:

- `AppServiceProvider` — Passport keys, token lifetimes
- `Notsoweb\LaravelCore\ServiceProvider` — boilerplate
- `Spatie\Permission\PermissionServiceProvider`

## Comandos útiles

```bash
php artisan config:show app.name
php artisan route:list --path=api
php artisan migrate:status
composer run db:dev      # Dev: migrate + seed + passport client
composer run services:start  # PM2: queue, scheduler, reverb
vendor/bin/pint --dirty  # Formateo PHP
php artisan test --compact
```
