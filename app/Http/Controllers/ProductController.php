<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\BranchScopeTrait;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
     use BranchScopeTrait;

    public function index(Request $request)
    {
        $rowsPerPage = $request->input('rowsPerPage', 10); // valor por defecto
        $page = $request->input('page', 1); // número de página
    
        $query = Product::with(['category', 'line', 'branch', 'shop', 'status'])
            ->orderBy('created_at', 'desc');
        $this->applyBranchScope($query);
        $products = $query->paginate($rowsPerPage, ['*'], 'page', $page);
    
        return $this->respondWithScope([
            "products" => $products
        ]);
    }

    public function indexNoPaginate(Request $request)
    {
        $products = Product::with(['category', 'line', 'branch', 'shop', 'status'])->where('status_id', 2)->get();
        return response()->json($products);
    }

    public function productsForSelect(Request $request)
    {
        $query = Product::with(['category', 'line', 'branch', 'shop', 'status'])->where('status_id', 2);
        $this->applyBranchScope($query);
        $products = $query->get();
        return $this->respondWithScope([
            "products" => $products
        ]);
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

    //total de gramos general dashboard Admin
    // ======= 📏 TOTAL GRAMOS =======
    public function totalGramos()
    {
        $total = $this->sumWithScope(Product::class, 'weight', [
            'deleted_at' => null,
        ]);

        return response()->json([
            'total_gramos' => $total,
            ...$this->getScopeType(),
        ]);
    }


    public function totalDineroGramos()
    {
        $query = Product::query()
            ->whereNotNull('weight')
            ->whereNull('deleted_at');
    
        // 👇 Aplica el filtro de sucursal si no es admin
        $this->applyBranchScope($query);
    
        // 👇 Obtén el total
        $total = $query->sum('price');
    
        // 👇 Devuelve con información del alcance (branch/global)
        return $this->respondWithScope([
            'total_dinero_gramos' => $total,
        ]);
    }

    // ======= 💎 GRAMOS EXISTENTES =======
    public function totalGramosExistentes()
    {
        $query = Product::query()
            ->whereNotNull('weight')
            ->where('status_id', 2)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);
        

        return $this->respondWithScope([
            'total_gramos_existentes' => $query->sum('weight'),
        ]);
    }

    public function totalDineroGramosExistentes()
    {
        $query = Product::query()
            ->whereNotNull('weight')
            ->where('status_id', 2)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_dinero_gramos_existentes' => $query->sum('price'),
        ]);
    }

    // ======= ⚠️ GRAMOS DAÑADOS =======
    public function totalGramosDanados()
    {
        $query = Product::query()
            ->whereNotNull('weight')
            ->where('status_id', 4)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_gramos_danados' => $query->sum('weight'),
        ]);
    }

    public function totalDineroGramosDanados()
    {
        $query = Product::query()
            ->whereNotNull('weight')
            ->where('status_id', 4)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_dinero_gramos_danados' => $query->sum('price'),
        ]);
    }

    // ======= 🔁 GRAMOS TRASPASADOS =======
    public function totalGramosTraspasados()
    {
        $query = Product::query()
            ->whereNotNull('weight')
            ->where('status_id', 3)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_gramos_traspasados' => $query->sum('weight'),
        ]);
    }

    public function totalDineroGramosTraspasados()
    {
        $query = Product::query()
            ->whereNotNull('weight')
            ->where('status_id', 3)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_dinero_gramos_traspasados' => $query->sum('price'),
        ]);
    }

    // ======= 💍 PIEZAS =======
    public function totalDineroPiezas()
    {
        $query = Product::query()
            ->whereNull('weight')
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_dinero_piezas' => $query->sum('price'),
        ]);
    }

    // ======= 🧮 PIEZAS EXISTENTES =======
    public function totalPiezasExistentes()
    {
        $query = Product::query()
            ->whereNull('weight')
            ->where('status_id', 2)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_piezas_existentes' => $query->count(), // No tiene sentido sumar weight aquí
        ]);
    }

    public function totalDineroPiezasExistentes()
    {
        $query = Product::query()
            ->whereNull('weight')
            ->where('status_id', 2)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_dinero_piezas_existentes' => $query->sum('price'),
        ]);
    }

    // ======= ⚠️ PIEZAS DAÑADAS =======
    public function totalPiezasDanados()
    {
        $query = Product::query()
            ->whereNull('weight')
            ->where('status_id', 4)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_piezas_danados' => $query->count(),
        ]);
    }

    public function totalDineroPiezasDanados()
    {
        $query = Product::query()
            ->whereNull('weight')
            ->where('status_id', 4)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_dinero_piezas_danados' => $query->sum('price'),
        ]);
    }

    // ======= 🔁 PIEZAS TRASPASADAS =======
    public function totalPiezasTraspasados()
    {
        $query = Product::query()
            ->whereNull('weight')
            ->where('status_id', 3)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_piezas_traspasados' => $query->count(),
        ]);
    }

    public function totalDineroPiezasTraspasados()
    {
        $query = Product::query()
            ->whereNull('weight')
            ->where('status_id', 3)
            ->whereNull('deleted_at');

        $this->applyBranchScope($query);

        return $this->respondWithScope([
            'total_dinero_piezas_traspasados' => $query->sum('price'),
        ]);
    }

}