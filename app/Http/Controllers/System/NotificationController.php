<?php namespace App\Http\Controllers\System;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Notificaciones del sistema
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class NotificationController extends Controller
{
    /**
     * Listar notificaciones del usuario
     */
    public function index(Request $request)
    {
        $model = Auth::user()->notifications()->orderByDesc('created_at');

        if ($request->filled('q')) {
            $query = $request->input('q');

            $model->where(function ($builder) use ($query) {
                $builder->where('data->title', 'LIKE', "%{$query}%")
                    ->orWhere('data->description', 'LIKE', "%{$query}%")
                    ->orWhere('data->message', 'LIKE', "%{$query}%");
            });
        }

        return ApiResponse::OK->response([
            'models' => $model->paginate(config('app.pagination')),
        ]);
    }

    /**
     * Obtener notificaciones no leídas recientes
     */
    public function allUnread(): JsonResponse
    {
        return ApiResponse::OK->response([
            'total' => Auth::user()->unreadNotifications()->count(),
            'unread_closed' => Auth::user()->unreadNotifications()->where('is_closed', true)->count(),
            'notifications' => Auth::user()->unreadNotifications()->where('is_closed', false)->limit(10)->get(),
        ]);
    }

    /**
     * Marcar notificación como leída
     */
    public function read(Request $request): JsonResponse
    {
        $notification = Notification::find($request->input('id'));

        if ($notification) {
            $notification->markAsRead();
        }

        return ApiResponse::OK->response();
    }

    /**
     * Marcar notificación como cerrada
     */
    public function close(Request $request): JsonResponse
    {
        $notification = Notification::find($request->input('id'));

        if ($notification) {
            $notification->markAsClosed();
        }

        return ApiResponse::OK->response();
    }

    /**
     * Eliminar notificación
     */
    public function destroy(Request $request): JsonResponse
    {
        $notification = Notification::find($request->input('id'));

        if ($notification) {
            $notification->delete();
        }

        return ApiResponse::OK->response();
    }
}
