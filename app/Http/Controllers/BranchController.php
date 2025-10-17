<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(){
        $branches = Branch::all();
        return response()->json(['branches' => $branches]);
    }
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'branch_name' => 'required|string|max:255',
                'legal_representative' => 'required|string|max:255',
                'email' => 'required|string|email|unique:branches,email|max:255',
                'rfc' => 'required|string|unique:branches,rfc|max:255',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'state_id' => 'required|exists:states,id',
                'municipality_id' => 'required|exists:municipalities,id',
            ]);
        
            $shop = $request->user()->shop;
            $branch = Branch::create([
                ...$validated,
                'shop_id' => $shop->id,
            ]);
        
            return response()->json(['message' => 'Sucursal creada exitosamente.', 'branch' => $branch], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $branch = Branch::findOrFail($id); // Busca la sucursal
        $branch->delete(); // Soft delete si el modelo usa SoftDeletes
    
        return response()->json([
            "message" => "La sucursal se ha eliminado correctamente"
        ], 200);
    }
}