<?php namespace Database\Seeders;
/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Models\PermissionType;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Notsoweb\LaravelCore\Traits\MySql\RolePermission;
use Spatie\Permission\Models\Permission;

/**
 * Roles predeterminados del sistema
 * 
 * @author Moisés Cortés C. <soy@mcortes.dev>
 * 
 * @version 1.0.0
 */
class RoleSeeder extends Seeder
{
    use RolePermission;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permisos de usuarios
        $users = PermissionType::firstOrCreate([
            'name' => 'Usuarios'
        ]);

        [
            $userIndex,
            $userCreate,
            $userEdit,
            $userDestroy
        ] = $this->onCRUD('users', $users, 'api');

        // Rol desarrollador
        Role::firstOrCreate([
            'name' => 'developer',
        ], [
            'description' => 'Desarrollador',
            'guard_name' => 'api'
        ])->syncPermissions(Permission::all());

        // Rol administrador
        Role::firstOrCreate([
            'name' => 'admin',
        ], [
            'description' => 'Administrador',
            'guard_name' => 'api'
        ])->syncPermissions(
            $userIndex,
            $userCreate,
            $userEdit,
            $userDestroy,
        );
    }
}
