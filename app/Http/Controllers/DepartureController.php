<?php

namespace App\Http\Controllers;

use App\Models\Departure;
use App\Models\DepartureDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use App\Traits\BranchScopeTrait;

class DepartureController extends Controller
{
       use BranchScopeTrait;
    public function index(Request $request)
    {
        $rowsPerPage = $request->input('rowsPerPage', 10); // valor por defecto
        $page = $request->input('page', 1); // número de página
        $query = Departure::with(['branch', 'user', 'details.product'])
        ->orderBy('id', 'desc');
        $this->applyBranchScope($query);
        $departures = $query->paginate($rowsPerPage, ['*'], 'page', $page);
        return $this->respondWithScope([
            "departures" => $departures
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'auth' => 'nullable|string',
            'recibe' => 'nullable|string',
            'description' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'details' => 'required|array',
            'details.*.product_id' => 'required|exists:products,id',
        ]);
    
        DB::beginTransaction();
    
        try {
            // Crear la salida
            $departure = Departure::create([
                'name' => $request->name,
                'auth' => $request->auth,
                'recibe' => $request->recibe,
                'description' => $request->description,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id(), // usuario autenticado
            ]);
        
            // Crear detalles y actualizar status del producto
            foreach ($request->details as $detail) {
                $product = Product::findOrFail($detail['product_id']);
            
                // Crear el detalle de salida
                DepartureDetail::create([
                    'departure_id' => $departure->id,
                    'product_id' => $product->id,
                ]);
            
                // Actualizar el status del producto
                $product->update(['status_id' => 6]);
            }
        
            DB::commit();
        
            return response()->json([
                'message' => 'Salida creada exitosamente',
                'departure' => $departure->load(['details.product']),
            ], 201);
        
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear la salida',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        $departure = Departure::with('details.product', 'details.product.line', 'details.product.category', 'branch')->findOrFail($id);
        return response()->json($departure);
    }

    public function destroy($id)
    {
        $departure = Departure::findOrFail($id);
        $departure->delete();

        return response()->json(['message' => 'Salida eliminada correctamente']);
    }


    public function generatePDF($id)
    {
        $departure = Departure::with('details.product', 'details.product.line', 'details.product.category', 'branch')->findOrFail($id);
        $pdf = Pdf::loadView('departures.pdf', compact('departure'))
            ->setPaper('letter', 'portrait');
    
        return new Response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="Salida-'.$departure->id.'.pdf"',
        ]);
    }
}