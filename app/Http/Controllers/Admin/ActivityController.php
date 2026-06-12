<?php namespace App\Http\Controllers\Admin;
/**
 * @copyright (c) 2026 MCortesDev (https://mcortes.dev) - All Rights Reserved
 */

use App\Http\Controllers\Controller;
use App\Http\Requests\Activity\ActivityIndexRequest;
use App\Models\LogEvent;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Historial de acciones del sistema
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class ActivityController extends Controller
{
    /**
     * Listar actividades
     */
    public function index(ActivityIndexRequest $request)
    {
        $filters = $request->validated();

        $model = LogEvent::with('user:id,name,paternal,maternal,profile_photo_path,deleted_at')
            ->when(! empty($filters['user']), function ($query) use ($filters) {
                $query->where('user_id', $filters['user']);
            })
            ->when(! empty($filters['search']), function ($query) use ($filters) {
                $query->where('event', 'like', '%'.$filters['search'].'%');
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters) {
                $query->where('created_at', '>=', "{$filters['start_date']} 00:00:00");
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters) {
                $query->where('created_at', '<=', "{$filters['end_date']} 23:59:59");
            });

        return ApiResponse::OK->response([
            'models' => $model->orderBy('created_at', 'desc')
                ->paginate(config('app.pagination')),
        ]);
    }
}
