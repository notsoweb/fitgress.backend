<?php

namespace App\Http\Controllers\Gym;

/**
 * @copyright (c) 2026 Mdev (https://mcortes.dev) - All rights reserved.
 */

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Registro;
use Carbon\CarbonImmutable;
use Notsoweb\ApiResponse\Enums\ApiResponse;

/**
 * Resumen del gimnasio para el dashboard
 *
 * Catálogo (máquinas y ejercicios) es global. Días de entrenamiento y
 * ejercicio más realizado son del usuario autenticado.
 *
 * @author Moisés Cortés C. <soy@mcortes.dev>
 *
 * @version 1.0.0
 */
class DashboardController extends Controller
{
    /**
     * Métricas de resumen para la página inicial
     */
    public function show()
    {
        $top = Registro::forUser()
            ->toBase()
            ->select('exercise_id')
            ->selectRaw('count(*) as times')
            ->groupBy('exercise_id')
            ->orderByDesc('times')
            ->orderBy('exercise_id')
            ->first();

        $topExercise = null;

        if ($top !== null) {
            $exercise = Exercise::query()
                ->with('machine:id,name,code')
                ->find($top->exercise_id, ['id', 'name', 'machine_id']);

            if ($exercise !== null) {
                $topExercise = [
                    'id' => $exercise->id,
                    'name' => $exercise->name,
                    'times' => (int) $top->times,
                    'machine' => $exercise->machine,
                ];
            }
        }

        return ApiResponse::OK->response([
            'machines_count' => Machine::query()->count(),
            'exercises_count' => Exercise::query()->count(),
            'workout_days' => (int) Registro::forUser()
                ->toBase()
                ->selectRaw('count(distinct date(performed_at)) as aggregate')
                ->value('aggregate'),
            'top_exercise' => $topExercise,
            'calendar' => $this->calendar(),
        ]);
    }

    /**
     * Días con entrenamiento y los planes ejecutados ese día
     *
     * @return array<int, array{date: string, plans: array<int, array{id: int|null, name: string|null}>}>
     */
    private function calendar(): array
    {
        $pairs = Registro::forUser()
            ->toBase()
            ->selectRaw('date(performed_at) as workout_date, plan_id')
            ->groupByRaw('date(performed_at), plan_id')
            ->orderBy('workout_date')
            ->get();

        if ($pairs->isEmpty()) {
            return [];
        }

        $plans = Plan::query()
            ->whereIn('id', $pairs->pluck('plan_id')->filter()->unique()->all())
            ->get(['id', 'name'])
            ->keyBy('id');

        return $pairs
            ->groupBy(fn ($row) => CarbonImmutable::parse($row->workout_date)->toDateString())
            ->map(function ($rows, string $date) use ($plans) {
                return [
                    'date' => $date,
                    'plans' => $rows->map(function ($row) use ($plans) {
                        if ($row->plan_id === null) {
                            return ['id' => null, 'name' => null];
                        }

                        $planId = (int) $row->plan_id;

                        return [
                            'id' => $planId,
                            'name' => $plans->get($planId)?->name,
                        ];
                    })->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
