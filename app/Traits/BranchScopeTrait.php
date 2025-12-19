<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

trait BranchScopeTrait
{
    /**
     * Aplica el filtro por sucursal al query, según el tipo de usuario.
     * Si es admin (type_user = 1), ve todos los registros.
     * Si no, filtra por branch_id del usuario.
     */
    protected function applyBranchScope(Builder $query, string $column = 'branch_id', ?string $relation = null): Builder
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }


        if ($user && $user->type_user_id !== 1) {
            if ($relation) {
                // Filtra usando una relación, por ejemplo: Sale tiene relación con branch
                $query->whereHas($relation, function ($q) use ($user, $column) {
                    $q->where($column, $user->branch_id);
                });
            } else {
                // Filtra directamente por columna branch_id
                $query->where($column, $user->branch_id);
            }
        }

        return $query;
    }

    /**
     * Obtiene el tipo de alcance de la consulta (global o por sucursal).
     */
    protected function getScopeType(): array
    {
        $user = Auth::user();

        return [
            'scope' => ($user && $user->type_user_id === 1) ? 'global' : 'branch',
            'branch_id' => $user->branch_id ?? null,
        ];
    }

    /**
     * Suma un campo en base al alcance y condiciones personalizadas.
     */
    protected function sumWithScope(string $model, string $column, array $conditions = [], ?string $relation = null): float
    {
        $query = $model::query();

        // Aplicar condiciones base
        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        // Aplicar el alcance de sucursal o global
        $this->applyBranchScope($query, 'branch_id', $relation);

        return (float) $query->sum($column);
    }

    /**
     * Cuenta registros en base al alcance.
     */
    protected function countWithScope(string $model, array $conditions = [], ?string $relation = null): int
    {
        $query = $model::query();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        $this->applyBranchScope($query, 'branch_id', $relation);

        return $query->count();
    }

    /**
     * Obtiene registros (get) en base al alcance.
     */
    protected function getWithScope(string $model, array $conditions = [], ?string $relation = null)
    {
        $query = $model::query();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        $this->applyBranchScope($query, 'branch_id', $relation);

        return $query->get();
    }

    /**
     * Obtiene registros paginados (paginate) en base al alcance.
     */
    protected function paginateWithScope(string $model, int $perPage = 10, array $conditions = [], ?string $relation = null)
    {
        $query = $model::query();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        $this->applyBranchScope($query, 'branch_id', $relation);

        return $query->paginate($perPage);
    }

    /**
     * Calcula un promedio en base al alcance.
     */
    protected function avgWithScope(string $model, string $column, array $conditions = [], ?string $relation = null): float
    {
        $query = $model::query();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        $this->applyBranchScope($query, 'branch_id', $relation);

        return (float) $query->avg($column);
    }

    protected function respondWithScope(array $data, int $status = 200)
    {
        return response()->json(array_merge(
            $data,
            $this->getScopeType() // 👈 añade 'scope' y 'branch_id'
        ), $status);
    }
}