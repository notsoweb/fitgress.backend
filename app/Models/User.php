<?php namespace App\Models;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Traits\HasProfilePhoto;
use App\Observers\UserObserver;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;
use Notsoweb\LaravelCore\Traits\Models\Extended;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\Permission\Traits\HasRoles;

/**
 * Usuarios
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
#[Fillable([
    'name',
    'paternal',
    'maternal',
    'phone',
    'email',
    'password',
    'profile_photo_path',
])]
#[Hidden([
    'password',
    'remember_token',
    'profile_photo_path',
])]
#[ObservedBy([
    UserObserver::class,
])]
class User extends Authenticatable implements HasPasskeys
{
    use Extended,
        HasApiTokens,
        HasFactory,
        HasProfilePhoto,
        HasRoles,
        InteractsWithPasskeys,
        Notifiable,
        // MustVerifyEmail,
        SoftDeletes;

    /**
     * Transformar atributos
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Atributos virtuales

    /**
     * Atributos que se deben agregar al modelo en su forma de array
     */
    protected $appends = [
        'full_name',
        'last_name',
        'profile_photo_url',
    ];

    /**
     * Nombre completo
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->name} {$this->paternal} {$this->maternal}",
        );
    }

    /**
     * Apellido paterno y materno
     */
    public function lastName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->paternal} {$this->maternal}",
        );
    }

    // Relaciones

    /**
     * Eventos realizados sobre usuarios
     */
    public function events()
    {
        return $this->morphMany(LogEvent::class, 'reportable');
    }

    /**
     * Un usuario puede generar muchos eventos
     *
     * Son los eventos que genera el usuario en el sistema.
     */
    public function history()
    {
        return $this->hasMany(LogEvent::class);
    }

    /**
     * Tokens de reseteo de contraseña
     */
    public function passwordResetTokens()
    {
        return $this->hasMany(PasswordResetToken::class);
    }

    // Acciones

    /**
     * Eliminar token de reseteo de contraseña
     */
    public function deletePasswordResetToken(string $token): bool
    {
        return $this->passwordResetTokens()->where('uuid', $token)->delete();
    }

    /**
     * Forzar contraseña
     */
    public function forcePassword(string $password): void
    {
        $this->update([
            'password' => bcrypt($password),
        ]);
    }

    /**
     * Generar token de reseteo de contraseña
     */
    public function generatePasswordResetToken(): string
    {
        if ($this->passwordResetTokens()->exists()) {
            $this->passwordResetTokens()->delete();
        }

        $passwordResetToken = $this->passwordResetTokens()->create();

        return $passwordResetToken->uuid;
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(Role::find(2));
    }

    /**
     * Verificar si el usuario es desarrollador
     */
    public function isDeveloper(): bool
    {
        return $this->hasRole(Role::find(1));
    }

    /**
     * Verificar si el usuario es primario (privilegios elevados)
     */
    public function isPrimary(): bool
    {
        return $this->hasRole(Role::find(1), Role::find(2));
    }

    /**
     * Validar contraseña
     */
    public function validatePassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }
}
