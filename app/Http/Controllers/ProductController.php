<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'line', 'branch', 'shop', 'status'])->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clave' => 'required|string|unique:products,clave',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'line_id' => 'nullable|exists:lines,id',
            'branch_id' => 'required|exists:branches,id',
            'shop_id' => 'required|exists:shops,id',
            'status_id' => 'nullable|exists:statuses,id',
            'weight' => ['nullable', 'regex:/^\d+(\.\d{1,2})?$/'],
            'observations' => 'nullable|string',
            'price_purchase' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'price_with_discount' => 'nullable|numeric|min:0',
        ]);

        $validated['status_id'] = $validated['status_id'] ?? 2;
        $shop = $request->user()->shop; // objeto Shop
        $shopId = $shop?->id;

        $product = Product::create([
            'clave' => $request->clave,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'line_id' => $request->line_id ?? null,
            'branch_id' => $request->branch_id,
            'shop_id' => $shopId,
            'status_id' => 2,
            'weight' => $request->weight ?? null,
            'observations' => $request->observations,
            'price_purchase' => $request->price_purchase,
            'price' => $request->price,
            'price_with_discount' => $request->price_with_discount,
        ]);

        return response()->json($product, 201);
    }

    public function show(Product $product)
    {
        $product->load(['category', 'line', 'branch', 'shop', 'status']);
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'clave' => 'sometimes|string|unique:products,clave,' . $product->id,
            'description' => 'sometimes|string',
            'category_id' => 'sometimes|exists:categories,id',
            'line_id' => 'nullable|exists:lines,id',
            'branch_id' => 'sometimes|exists:branches,id',
            'shop_id' => 'sometimes|exists:shops,id',
            'status_id' => 'nullable|exists:statuses,id',
            'weight' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'price_purchase' => 'sometimes|numeric|min:0',
            'price' => 'sometimes|numeric|min:0',
            'price_with_discount' => 'nullable|numeric|min:0',
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}