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
        $request->validate([
            'branch_name' => 'required|string|max:255',
            'legal_representative' => 'required|string|max:255',
            'email' => 'required|string|email|unique:branches,email|max:255',
            'rfc' => 'required|string|unique:branches,rfc|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            // 'shop_id' => 'required|exists:shops,id',
            'state_id' => 'required|exists:states,id',
            'municipality_id' => 'required|exists:municipalities,id',
        ]);
$shop = $request->user()->shop; // objeto Shop
$shopId = $shop?->id;
        $branch = Branch::create([
            'branch_name' => $request->branch_name,
            'legal_representative' => $request->legal_representative,
            'email' => $request->email,
            'rfc' => $request->rfc,
            'phone' => $request->phone,
            'address' => $request->address,
            'state_id' => $request->state_id,
            'municipality_id' => $request->municipality_id,
            'shop_id' => $shopId,
        ]);
        $branch->save();
        return response()->json(['message' => 'Sucursal creada exitosamente.', 'branch' => $branch], 201);
    }
}