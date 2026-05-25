<?php namespace App\Http\Traits;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All rights reserved
 */

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Trait para manejar la foto de perfil
 * 
 * @author Moisés Cortés C <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
trait HasProfilePhoto
{
    /**
     * Actualizar la foto de perfil del usuario
     */
    public function updateProfilePhoto(UploadedFile $photo, $storagePath = 'photos') : void
    {
        tap($this->profile_photo_path, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'profile_photo_path' => $photo->storePublicly(
                    $storagePath, ['disk' => $this->profilePhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->profilePhotoDisk())->delete($previous);
            }
        });
    }

    /**
     * Eliminar la foto de perfil del usuario
     */
    public function deleteProfilePhoto() : void
    {
        if (is_null($this->profile_photo_path)) {
            return;
        }

        Storage::disk($this->profilePhotoDisk())->delete($this->profile_photo_path);

        $this->forceFill([
            'profile_photo_path' => null,
        ])->save();
    }

    /**
     * Obtener la URL de la foto de perfil del usuario
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->profile_photo_path
                    ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
        });
    }

    /**
     * Obtener la URL de la foto de perfil por defecto si no se ha subido ninguna
     */
    protected function defaultProfilePhotoUrl() : string
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Obtener el disco donde se deben almacenar las fotos de perfil
     */
    protected function profilePhotoDisk() : string
    {
        return config('app.profile_disk', 'profile');
    }
}
