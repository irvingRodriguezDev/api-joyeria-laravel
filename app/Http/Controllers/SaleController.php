<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleStoreRequest;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
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
}