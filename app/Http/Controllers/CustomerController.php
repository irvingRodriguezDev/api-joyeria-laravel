<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Traits\BranchScopeTrait;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
   use BranchScopeTrait;
    
    public function index(Request $request)
    {
        $rowsPerPage = $request->input('rowsPerPage', 10);
        $page = $request->input('page', 1);
        
        // Base query con relaciones
        $query = Customer::with(['branch', 'shop'])
            ->whereNull('deleted_at'); // buena práctica si manejas borrado lógico
        
        // Aplica el alcance según el tipo de usuario (admin o vendedor)
        $this->applyBranchScope($query);
        
        // Pagina resultados
        $customers = $query->paginate($rowsPerPage, ['*'], 'page', $page);
        
        // Retorna la respuesta con contexto
        return $this->respondWithScope([
            'customers' => $customers,
        ]);
    }

    public function indexPerBranch(Request $request){
        $customers = Customer::with(['branch', 'shop'])->get();
        return response()->json($customers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $shopId = $request->user()->shop->id ?? null;

        if (!$shopId) {
            return response()->json(['error' => 'El usuario no tiene una tienda asociada.'], 422);
        }

        $validated['shop_id'] = $shopId;
        $validated['positive_balance'] = 0;

        $customer = Customer::create($validated);

        return response()->json($customer, 201);
    }

    public function show(Customer $customer)
    {
        $customer->load(['branch', 'shop']);
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'lastname' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'branch_id' => 'sometimes|exists:branches,id',
            'positive_balance' => 'sometimes|numeric|min:0',
        ]);

        $customer->update($validated);

        return response()->json($customer);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json(null, 204);
    }
}