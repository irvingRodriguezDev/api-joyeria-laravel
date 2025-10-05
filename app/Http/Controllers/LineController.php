<?php

namespace App\Http\Controllers;

use App\Models\Line;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lines = Line::all(); // o ->get()
        return response()->json($lines);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price_purchase' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'percent_discount' => 'nullable|numeric|between:0,100',
        ]);
        $shop = $request->user()->shop; // objeto Shop
        $shopId = $shop?->id;

        $line = Line::create([
            'name' => $request->name,
            'price_purchase' => $request->price_purchase,
            'price' => $request->price,
            'percent_discount' => $request->percent_discount,
            'shop_id' => $shopId,
        ]);

        $line->save();

        return response()->json([
            'message' => 'Line creada correctamente',
            'data' => $line
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Line $line)
    {
        return response()->json($line);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Line $line)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price_purchase' => 'sometimes|required|numeric|min:0',
            'price' => 'sometimes|required|numeric|min:0',
            'percent_discount' => 'nullable|numeric|between:0,100',
        ]);

        $line->update($validated);

        return response()->json([
            'message' => 'Line actualizada correctamente',
            'data' => $line
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Line $line)
    {
        $line->delete();
        return response()->json([
            'message' => 'Line eliminada correctamente'
        ], Response::HTTP_NO_CONTENT);
    }
}