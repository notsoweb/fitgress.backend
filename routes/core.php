<?php
/*
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\PermissionTypeController as AdminPermissionTypeController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\System\NotificationController;
use App\Http\Controllers\System\PermissionController;
use App\Http\Controllers\System\PermissionTypeController;
use App\Http\Controllers\System\RoleController;
use App\Http\Controllers\UserController as MyUserController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del núcleo de la aplicación.
 *
 * Se recomienda que no se modifiquen estas rutas a menos que sepa lo que está haciendo.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
Route::get('/', [ServerController::class, 'status'])->name('status');

Route::middleware('auth:api')->group(function () {
    // Aplicación
    Route::prefix('user')->name('user.')->group(function () {
        Route::get('/', [MyUserController::class, 'show'])->name('show');
        Route::put('/', [MyUserController::class, 'update'])->name('update');
        Route::delete('/', [MyUserController::class, 'destroy'])->name('destroy');
        Route::put('password', [MyUserController::class, 'updatePassword'])->name('password');
        Route::post('password-confirm', [MyUserController::class, 'confirmPassword'])->name('password-confirm');
        Route::get('permissions', [MyUserController::class, 'permissions'])->name('permissions');
        Route::delete('photo', [MyUserController::class, 'destroyPhoto'])->name('photo');
        Route::get('roles', [MyUserController::class, 'roles'])->name('roles');
    });

    // Administración
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('activities', ActivityController::class)->only(['index']);

        Route::prefix('users/{user}')->name('users.')->group(function () {
            Route::put('password', [UserController::class, 'updatePassword'])->name('password');
            Route::get('permissions', [UserController::class, 'permissions'])->name('permissions');
            Route::get('roles', [UserController::class, 'roles'])->name('roles');
            Route::put('roles', [UserController::class, 'updateRoles'])->name('roles');
        });
        Route::apiResource('users', UserController::class);

        Route::apiResource('roles', AdminRoleController::class);
        Route::get('roles/{role}/permissions', [AdminRoleController::class, 'permissions'])->name('roles.permissions');
        Route::put('roles/{role}/permissions', [AdminRoleController::class, 'updatePermissions'])->name('roles.permissions.update');
    });

    Route::prefix('permission-types')->name('permission-types.')->group(function () {
        Route::get('all-with-permissions', [AdminPermissionTypeController::class, 'allWithPermissions'])->name('all-with-permissions');
    });

    // Rutas de sistema
    Route::prefix('system')->name('system.')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions');
        Route::get('permission-types', [PermissionTypeController::class, 'index'])->name('permission-types');
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
        });
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('all', [NotificationController::class, 'index'])->name('all');
            Route::get('all-unread', [NotificationController::class, 'allUnread'])->name('all-unread');
            Route::post('read', [NotificationController::class, 'read'])->name('read');
            Route::post('close', [NotificationController::class, 'close'])->name('close');
            Route::delete('destroy', [NotificationController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });

    Route::prefix('resources')->name('resources.')->group(function () {
        Route::post('get', [ResourceController::class, 'get'])->name('get');
    });

    // Información del sistema
    Route::get('changelogs', ChangelogController::class)->name('changelogs');
    Route::get('version', [ServerController::class, 'version'])->name('version');
});

Route::prefix('resources')->name('resources.')->group(function () {
    Route::get('app', [ResourceController::class, 'app'])->name('app');
    Route::get('routes', [ResourceController::class, 'routes'])->name('routes');
});

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('reset-password');
});
