# Módulo: Autenticación

Autenticación API mediante **Laravel Passport** (email/contraseña) y **passkeys WebAuthn** (Spatie Laravel Passkeys). Incluye recuperación de contraseña.

## Archivos clave

| Archivo | Rol |
|---------|-----|
| `app/Http/Controllers/AuthController.php` | Login, logout, forgot/reset password |
| `app/Http/Controllers/PasskeyController.php` | CRUD passkeys y login sin contraseña |
| `app/Actions/Passkeys/GeneratePasskeyRegisterOptionsAction.php` | Opciones personalizadas de registro |
| `app/Http/Requests/Auth/*` | Validación de login y reset |
| `app/Http/Requests/Passkey/*` | Validación WebAuthn |
| `app/Notifications/Auth/ForgotPasswordNotification.php` | Email de recuperación |
| `app/Models/PasswordResetToken.php` | Tokens UUID de reset |
| `app/Providers/AppServiceProvider.php` | Lifetimes de tokens Passport |

## Rutas API

### Públicas (guest)

| Método | Ruta | Nombre | Descripción |
|--------|------|--------|-------------|
| POST | `/api/auth/login` | `auth.login` | Login email/password → access token |
| POST | `/api/auth/forgot-password` | `auth.forgot-password` | Envía email de recuperación |
| POST | `/api/auth/reset-password` | `auth.reset-password` | Restablece contraseña con token |
| GET | `/api/auth/passkeys/authentication-options` | `auth.passkeys.authentication-options` | Opciones WebAuthn para login |
| POST | `/api/auth/passkeys/login` | `auth.passkeys.login` | Login con passkey |

### Autenticadas

| Método | Ruta | Nombre | Descripción |
|--------|------|--------|-------------|
| POST | `/api/auth/logout` | `auth.logout` | Revoca token actual |
| GET | `/api/user/passkeys` | `user.passkeys.index` | Lista passkeys del usuario |
| GET | `/api/user/passkeys/register-options` | `user.passkeys.register-options` | Opciones para registrar passkey |
| POST | `/api/user/passkeys` | `user.passkeys.store` | Registra nueva passkey |
| DELETE | `/api/user/passkeys/{passkey}` | `user.passkeys.destroy` | Elimina passkey |

## Configuración de tokens

| Tipo | Duración |
|------|----------|
| Access token | 30 días |
| Refresh token | 60 días |
| Personal access token | 12 meses |

Claves OAuth en `storage/app/keys` (o variables `PASSPORT_PRIVATE_KEY` / `PASSPORT_PUBLIC_KEY`).

## Conexión con el frontend

| Backend | Frontend |
|---------|----------|
| `auth.login` | `pages/Auth/Login.vue` |
| `auth.forgot-password` | `pages/Auth/ForgotPassword.vue` |
| `auth.reset-password` | `pages/Auth/ResetPassword.vue` |
| `auth.passkeys.*`, `user.passkeys.*` | `pages/Profile/Partials/PasskeysForm.vue` + `services/Passkeys.js` |

El guard de la SPA (`App.vue`) redirige a `auth.index` si no hay token (`hasToken()`).

## Relaciones con otros módulos

- **Users**: el login devuelve el modelo `User` con roles; el perfil se gestiona en `UserController`.
- **Admin**: usuarios autenticados con permisos Spatie acceden a rutas `admin.*`.
- **Passkeys**: requiere middleware de sesión en API (`StartSession`) para el estado de la ceremonia WebAuthn.
