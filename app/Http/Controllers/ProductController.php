<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $rowsPerPage = $request->input('rowsPerPage', 10); // valor por defecto
        $page = $request->input('page', 1); // número de página
    
        $products = Product::with(['category', 'line', 'branch', 'shop', 'status'])
            ->orderBy('created_at', 'desc')
            ->paginate($rowsPerPage, ['*'], 'page', $page);
    
        return response()->json($products);
    }

    public function indexNoPaginate(Request $request)
    {
        $products = Product::with(['category', 'line', 'branch', 'shop', 'status'])->where('status_id', 2)->get();
        return response()->json($products);
    }

    public function productsForSelect(Request $request)
    {
        $products = Product::with(['category', 'line', 'branch', 'shop', 'status'])->where('status_id', 2)->get();
        return response()->json($products);
    }


    public function ProductsByStatus($id)
    {
        $products = Product::with(['category', 'line', 'branch', 'shop', 'status'])
            ->where('status_id', $id)
            ->get();
    
        return response()->json($products);
    }

    public function ProductsAvailablePerBranch($id){
        $products = Product::with(['category', 'line', 'branch', 'shop', 'status'])
            ->where('status_id', 2)
            ->where('branch_id', $id)
            ->get();
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

    public function show($id)
    {
        $product = Product::with([
            'category:id,name',
            'line:id,name',
            'branch:id,branch_name',
            'shop:id,name',
            'status:id,name',
            'saleDetails:id,product_id,sale_id,quantity,final_price',
            'saleDetails.sale:id,client_id,total,created_at,folio,paid_out',
            'saleDetails.sale.client:id,name,lastname,phone'
        ])
        ->findOrFail($id);

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

    //total de gramos general
    public function totalGramos()
    {
        $totalGramos = Product::whereNotNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->sum('weight');
    
        return response()->json([
            'total_gramos' => $totalGramos
        ]);
    }

    public function totalDineroGramos()
    {
        $totalDineroGramos = Product::whereNotNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->sum('price');
    
        return response()->json([
            'total_dinero_gramos' => $totalDineroGramos
        ]);
    }
    //productosExistentes
    public function totalGramosExistentes()
    {
        $totalGramosExistentes = Product::whereNotNull('weight') // solo productos con gramos
        ->where('status_id', 2)  
        ->where('deleted_at', null)  
        ->sum('weight');
    
        return response()->json([
            'total_gramos_existentes' => $totalGramosExistentes
        ]);
    }
    public function TotalDineroGramosExistentes()
    {
        $totalDineroGramosExistentes = Product::whereNotNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 2)
            ->sum('price');
    
        return response()->json([
            'total_dinero_gramos_existentes' => $totalDineroGramosExistentes
        ]);
        
    }

    //totales gramos dañados
    public function totalGramosDanados(){
            $totalGramosDanados = Product::whereNotNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 4)
            ->sum('weight');
    
        return response()->json([
            'total_gramos_danados' => $totalGramosDanados
        ]);
    }
    public function totalDineroGramosDanados(){
            $totalDineroGramosDanados = Product::whereNotNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 4)
            ->sum('price');
    
        return response()->json([
            'total_dinero_gramos_danados' => $totalDineroGramosDanados
        ]);
    }

    //productos transpasados
    public function totalGramosTraspasados(){
            $totalGramosTraspasados = Product::whereNotNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 3)
            ->sum('weight');
    
        return response()->json([
            'total_gramos_traspasados' => $totalGramosTraspasados
        ]);
    }
    public function totalDineroGramosTraspasados(){
            $totalDineroGramosTraspasados = Product::whereNotNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 3)
            ->sum('price');
    
        return response()->json([
            'total_dinero_gramos_traspasados' => $totalDineroGramosTraspasados
        ]);
    }

    //piezas 
        public function totalDineroPiezas()
    {
        $totalDineroPiezas = Product::whereNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->sum('price');
    
        return response()->json([
            'total_dinero_piezas' => $totalDineroPiezas
        ]);
    }
    //productosExistentes
    public function totalPiezasExistentes()
    {
        $totalPiezasExistentes = Product::whereNull('weight') // solo productos con gramos
        ->where('status_id', 2)  
        ->where('deleted_at', null)  
        ->sum('weight');
    
        return response()->json([
            'total_piezas_existentes' => $totalPiezasExistentes
        ]);
    }
    public function TotalDineroPiezasExistentes()
    {
        $totalDineroPiezasExistentes = Product::whereNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 2)
            ->sum('price');
    
        return response()->json([
            'total_dinero_piezas_existentes' => $totalDineroPiezasExistentes
        ]);
        
    }

    //totales gramos dañados
    public function totalPiezasDanados(){
            $totalPiezasDanados = Product::whereNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 4)
            ->sum('weight');
    
        return response()->json([
            'total_piezas_danados' => $totalPiezasDanados
        ]);
    }
    public function totalDineroPiezasDanados(){
            $totalDineroPiezasDanados = Product::whereNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 4)
            ->sum('price');
    
        return response()->json([
            'total_dinero_piezas_danados' => $totalDineroPiezasDanados
        ]);
    }

    //productos transpasados
    public function totalPiezasTraspasados(){
            $totalGramosTraspasados = Product::whereNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 3)
            ->sum('weight');
    
        return response()->json([
            'total_piezas_traspasados' => $totalPiezasTraspasados
        ]);
    }
    public function totalDineroPiezasTraspasados(){
            $totalDineroPiezasTraspasados = Product::whereNull('weight') // solo productos con gramos
            ->where('deleted_at', null)
            ->where('status_id', 3)
            ->sum('price');
    
        return response()->json([
            'total_dinero_piezas_traspasados' => $totalDineroPiezasTraspasados
        ]);
    }

}