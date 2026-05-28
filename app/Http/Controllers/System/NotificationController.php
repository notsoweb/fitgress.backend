<?php namespace App\Http\Controllers\System;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;
use App\Models\Notification;
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
    //| Listar notificaciones del sistema
    public function index(Request $request)
    {
        $model = Notification::orderBy('created_at');

        if($request->has('q')) {
            $model = $model->where('title', 'LIKE', "%{$request->q}%")
                ->orWhere('message', 'LIKE', "%{$request->q}%");
        }

        return ApiResponse::OK->response([
            'notifications' => $model->paginate(config('app.pagination'))
        ]);
    }

    /**
     * Obtener notificaciones no leídas recientes
     */
    public function unreaded()
    {
        return ApiResponse::OK->response([
            'total' => Auth::user()->unreadNotifications()->count(),
            'unread_closed' => Auth::user()->unreadNotifications()->where('is_closed', true)->count(),
            'notifications' => Auth::user()->unreadNotifications()->limit(10)->get()
        ]);
    }

    //| Marcar notificación como leída
    public function read(Request $request)
    {
        $notification = Notification::find($request->id);
        $notification->markAsRead();
        return ApiResponse::OK->response();
    }

    //| Marcar notificación como cerrada
    public function close(Request $request)
    {
        $notification = Notification::find($request->id);
        $notification->markAsClosed();
        return ApiResponse::OK->response();
    }

    //| Eliminar notificación
    public function destroy(Request $request)
    {
        $notification = Notification::find($request->id);
        $notification->delete();
        return ApiResponse::OK->response();
    }
}
