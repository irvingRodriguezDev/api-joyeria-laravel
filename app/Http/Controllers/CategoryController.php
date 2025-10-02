<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    $validatedData = $request->validate([
        'name' => 'required|string|max:255',
        'type_product_id' => 'required|exists:typeproducts,id',
        'business_rule_id' => 'nullable|exists:business_rules,id', // opcional
    ]);

    $category = Category::create([
        'name' => $validatedData['name'],
        'type_product_id' => $validatedData['type_product_id'],
        'business_rule_id' => $validatedData['business_rule_id'] ?? null,
    ]);

    return response()->json([
        'message' => 'Categoría creada correctamente',
        'category' => $category
    ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}