<?php

namespace Database\Seeders;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Notsoweb\LaravelCore\Supports\UserSecureSupport;

/**
 * Usuarios predeterminados del sistema
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener dominio de correo
        $domain = config('mail.domain');

        // Generar usuario desarrollador
        $developer = UserSecureSupport::create("dev@{$domain}");

        User::create([
            'name' => 'Developer',
            'paternal' => 'MDev',
            'maternal' => '',
            'email' => $developer->email,
            'password' => $developer->hash,
        ])->assignRole(Role::find(1));

        // Generar usuario administrador
        $admin = UserSecureSupport::create("admin@{$domain}");

        User::create([
            'name' => 'Admin',
            'paternal' => 'MDev',
            'maternal' => '',
            'email' => $admin->email,
            'password' => $admin->hash,
        ])->assignRole(Role::find(2));

        // Generar usuario demo
        $demo = UserSecureSupport::create("demo@{$domain}");

        User::create([
            'name' => 'Demo',
            'paternal' => 'MDev',
            'maternal' => '',
            'email' => $demo->email,
            'password' => $demo->hash,
        ]);
    }
}
