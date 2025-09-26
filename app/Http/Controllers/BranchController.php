<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'legal_representative' => 'required|string|max:255',
            'email' => 'required|string|email|unique:branches,email|max:255',
            'rfc' => 'required|string|unique:branches,rfc|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'shop_id' => 'required|exists:shops,id',
            'state_id' => 'required|exists:states,id',
            'municipality_id' => 'required|exists:municipalities,id',
        ]);

        $branch = Branch::create($request->all());

        return response()->json(['message' => 'Sucursal creada exitosamente.', 'branch' => $branch], 201);
    }
}