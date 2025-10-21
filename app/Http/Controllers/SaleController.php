<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleStoreRequest;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request){
                $rowsPerPage = $request->input('rowsPerPage', 10); // valor por defecto
        $page = $request->input('page', 1); // número de página
        $sales = Sale::with('details', 'payments', 'client', 'branch')
        ->orderBy('folio', 'desc')
        ->paginate($rowsPerPage, ['*'], 'page', $page);
        return response()->json($sales);
    }

    public function store(SaleStoreRequest $request): JsonResponse
    {
    $data = $request->validated();

    DB::beginTransaction();

    try {
        // Generar folio consecutivo por branch_id
        $branchId = $data['branch_id'];
        $maxFolio = DB::table('sales')->where('branch_id', $branchId)->max('folio');
        $nextFolio = ($maxFolio !== null) ? ((int)$maxFolio + 1) : 1;

        // Crear venta
        $sale = Sale::create([
            'client_id' => $data['client_id'],
            'branch_id' => $branchId,
            'user_id'   => $data['user_id'],
            'folio'     => $nextFolio,
            'total'     => $data['total'],
            'paid_out'  => $data['paid_out'] ?? 0,
        ]);

        // Preparar detalles
        $detailsPayload = [];
        $productIds = [];

        foreach ($data['productsList'] as $prod) {
            $quantity = isset($prod['quantity']) ? (int)$prod['quantity'] : 1;
            $finalPrice = (float)$prod['final_price'];
            $pricePurchase = (float)$prod['price_purchase'];
            $profit = round(($finalPrice - $pricePurchase) * $quantity, 2);

            $detailsPayload[] = [
                'sale_id' => $sale->id,
                'product_id' => $prod['product_id'],
                'final_price' => $finalPrice,
                'price_purchase' => $pricePurchase,
                'profit' => $profit,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Guardar IDs de productos vendidos
            $productIds[] = $prod['product_id'];
        }

        // Insertar detalles
        if (!empty($detailsPayload)) {
            SaleDetail::insert($detailsPayload);
        }

        // ✅ Actualizar productos vendidos → status_id = 1 (vendido)
        if (!empty($productIds)) {
            DB::table('products')
                ->whereIn('id', $productIds)
                ->update([
                    'status_id' => 1, // ← 1 = vendido
                    'sold_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        // Insertar pagos (si los hay)
        if (!empty($data['payments'])) {
            $paymentsPayload = [];
            foreach ($data['payments'] as $p) {
                $paymentsPayload[] = [
                    'sale_id' => $sale->id,
                    'amount' => $p['amount'],
                    'payment_method' => $p['payment_method'] ?? null,
                    'reference' => $p['reference'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($paymentsPayload)) {
                Payment::insert($paymentsPayload);
            }
        }

        DB::commit();

        // Cargar relaciones para la respuesta
        $sale->load('details', 'payments');

        return response()->json([
            'success' => true,
            'sale' => $sale,
        ], 201);
    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error creating sale',
            'error' => $e->getMessage(),
        ], 500);
    }
    }
    public function generateTicket($id)
    {
        // Traemos la venta con todas sus relaciones
        $sale = Sale::with('details.product', 'payments', 'client', 'branch')
                    ->findOrFail($id);
        // return $sale;
        // Cargamos la vista Blade con los datos de la venta
        $pdf = Pdf::loadView('tickets.sale', compact('sale'));
    
        // Opcional: establecer tamaño tipo ticket térmico (80mm)
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm aprox de ancho
    
        // Mostrar el PDF en el navegador
        return $pdf->stream("ticket_venta_{$sale->id}.pdf");
    }

    public function showSale($id){
              $sale = Sale::with('details.product', 'details.product.line', 'details.product.category', 'payments', 'client', 'branch')
                    ->findOrFail($id);
                    return response()->json($sale);
    }

    public function totalVendidoHoy(): JsonResponse
    {
        $total = Sale::whereDate('created_at', Carbon::today())->sum('total');

        return response()->json([
            'total_vendido_hoy' => $total,
            'fecha' => Carbon::today()->toDateString()
        ]);
    }

    public function totalVendidoSemana(): JsonResponse
    {
        $inicioSemana = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $finSemana = Carbon::now()->endOfWeek(Carbon::SUNDAY);

        $total = Sale::whereBetween('created_at', [$inicioSemana, $finSemana])
                     ->sum('total');

        return response()->json([
            'total_vendido_semana' => $total,
            'rango' => [
                'inicio' => $inicioSemana->toDateString(),
                'fin' => $finSemana->toDateString(),
            ]
        ]);
    }

    public function totalVendidoMes(): JsonResponse
    {
        $inicioMes = Carbon::now()->startOfMonth();
        $finMes = Carbon::now()->endOfMonth();

        $total = Sale::whereBetween('created_at', [$inicioMes, $finMes])
                     ->sum('total');

        return response()->json([
            'total_vendido_mes' => $total,
            'rango' => [
                'inicio' => $inicioMes->toDateString(),
                'fin' => $finMes->toDateString(),
            ]
        ]);
    }

}