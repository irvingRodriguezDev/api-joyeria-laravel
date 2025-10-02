<?php

namespace App\Http\Controllers;

use App\Models\BusinessRule;
use Illuminate\Http\Request;

class BusinessRuleController extends Controller
{
    // Listar todas las reglas
    public function index()
    {
        $rules = BusinessRule::all();
        return response()->json($rules);
    }

    // Crear nueva regla
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'operator' => 'required|string|max:255',
            'multiplicator' => 'required|integer|max:255',
            'percent_discount' => 'required|integer|max:255',
        ]);

        $rule = BusinessRule::create($validatedData);

        return response()->json([
            'message' => 'Regla de negocio creada correctamente',
            'rule' => $rule
        ], 201);
    }
    
    // Mostrar una regla específica
    public function show($id)
    {
        $rule = BusinessRule::findOrFail($id);
        return response()->json($rule);
    }

    // Actualizar regla existente
    public function update(Request $request, $id)
    {
        $rule = BusinessRule::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $rule->update($validatedData);

        return response()->json([
            'message' => 'Regla de negocio actualizada correctamente',
            'rule' => $rule
        ]);
    }

    // Eliminar regla
    public function destroy($id)
    {
        $rule = BusinessRule::findOrFail($id);
        $rule->delete();

        return response()->json([
            'message' => 'Regla de negocio eliminada correctamente'
        ]);
    }
}