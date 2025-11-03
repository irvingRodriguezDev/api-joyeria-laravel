<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AppliesBranchScope
{
    /**
     * Aplica un filtro de sucursal a la consulta, según el tipo de usuario.
     * 
     * - Si el usuario es admin (type_user = 1), no se aplica ningún filtro.
     * - Si el usuario es vendedor (type_user = 2), se filtra por su branch_id.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyBranchScope($query)
    {
        $user = Auth::user();

        if ($user && $user->type_user_id === 3 && !empty($user->branch_id)) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }
}