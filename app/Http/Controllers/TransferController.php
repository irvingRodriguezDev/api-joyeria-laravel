<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransferRequest;
use App\Http\Requests\RespondTransferRequest;
use App\Models\Product;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    // ========================================================
    //  🔵 1. CREAR TRASPASO
    // ========================================================
    public function store(CreateTransferRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
    
        $newBranchId = $data['new_branch_id'];
        $products = $data['products'];
    
        DB::beginTransaction();
    
        try {
            $createdTransfers = []; // <--- Guarda aquí los registros creados
        
            foreach ($products as $prod) {
                $product = Product::lockForUpdate()->find($prod['product_id']);
            
                if ($product->status_id != 2)
                    throw new \Exception("El producto {$product->id} no está disponible para traspaso.");
            
                if ($user->type_user_id == 3 && $product->branch_id != $user->branch_id)
                    throw new \Exception("No puedes traspasar un producto que no pertenece a tu sucursal.");
            
                // Crear registro
                $transfer = Transfer::create([
                    'product_id'       => $product->id,
                    'last_branch_id'   => $product->branch_id,
                    'new_branch_id'    => $newBranchId,
                    'status'           => 1,
                    'user_origin_id'   => $user->id,
                ]);
            
                // Guardar para la respuesta
                $createdTransfers[] = $transfer;
            
                // Cambiar estado del producto mientras está en tránsito
                $product->update([
                    'status_id' => 3,
                ]);
            }
        
            DB::commit();
        
            // Devolver los registros creados
            return response()->json([
                'success' => true,
                'transfers' => $createdTransfers
            ], 200);
        
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    // ========================================================
    // 🔵 2. ACEPTAR / RECHAZAR / CANCELAR
    // ========================================================
    public function respond(RespondTransferRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
    
        DB::beginTransaction();
    
        try {
            $updatedTransfers = [];
        
            $messages = [
                'accept' => 'Traspaso aceptado correctamente.',
                'reject' => 'Traspaso rechazado correctamente.',
                'cancel' => 'Traspaso cancelado correctamente.',
            ];
        
            foreach ($data['transfer_ids'] as $id) {
                $tr = Transfer::lockForUpdate()->findOrFail($id);
                $product = Product::lockForUpdate()->findOrFail($tr->product_id);
            
                if ($data['action'] === "accept") {
                    $tr->update([
                        'status' => 2,
                        'user_destination_id' => $user->id,
                    ]);
                
                    $product->update([
                        'status_id' => 2,
                        'branch_id' => $tr->new_branch_id,
                    ]);
                }
            
                if ($data['action'] === "reject") {
                    $tr->update([
                        'status' => 3,
                        'user_destination_id' => $user->id,
                    ]);
                
                    $product->update([
                        'status_id' => 2,
                        'branch_id' => $tr->last_branch_id,
                    ]);
                }
            
                if ($data['action'] === "cancel") {
                    if ($tr->status != 1) {
                        throw new \Exception("El traspaso ya no puede cancelarse.");
                    }
                
                    $tr->update(['status' => 4]);
                
                    $product->update([
                        'status_id' => 2,
                        'branch_id' => $tr->last_branch_id,
                    ]);
                }
            
                $updatedTransfers[] = Transfer::with([
                    'originBranch',
                    'destinationBranch',
                    'originUser',
                    'destinationUser',
                    'product'
                ])->find($tr->id);
            }
        
            DB::commit();
        
            return response()->json([
                'success' => true,
                'message' => $messages[$data['action']] ?? 'Acción realizada correctamente.',
                'updated_transfers' => $updatedTransfers,
            ]);
        
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }



    // ========================================================
    // 🔵 3. HISTORIAL (entrantes + salientes)
    // ========================================================
    public function history($branchId, Request $request)
    {
        $user = auth()->user();

        if ($user->type_user_id == 3) {
            $branchId = $user->branch_id;
        }

        return Transfer::with(['product','originBranch','newBranch'])
            ->where(function ($q) use ($branchId) {
                $q->where('last_branch_id', $branchId)
                  ->orWhere('new_branch_id', $branchId);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(20);
    }

    // ========================================================
    // 🔵 4. SALIENTES (sucursal que envía)
    // ========================================================
    public function outgoing($branchId, Request $request)
    {
        $user = auth()->user();
    
        // Vendedor: forzamos su sucursal
        if ($user->type_user_id == 3) {
            $branchId = $user->branch_id;
        }
    
        // Leer per_page desde query string
        $perPage = $request->get('limit', 20);
    
        return Transfer::with(['product', 'product.category','product.line','destinationBranch', 'originBranch', 'originUser'])
            ->where('last_branch_id', $branchId)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }
    

    // ========================================================
    // 🔵 5. ENTRANTES (sucursal que recibe)
    // ========================================================
    public function incoming($branchId, Request $request)
    {
        $user = auth()->user();
    
        if ($user->type_user_id == 3) {
            $branchId = $user->branch_id;
        }
    
        $perPage = $request->get('limit', 20);
    
        return Transfer::with(['product','product.category', 'product.line' ,'originBranch', 'destinationBranch', 'originUser'])
            ->where('new_branch_id', $branchId)
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);
    }

}