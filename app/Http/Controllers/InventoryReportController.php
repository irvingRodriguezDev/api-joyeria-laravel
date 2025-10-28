<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Status;

class InventoryReportController extends Controller
{
    public function generatePdf(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'status_id' => 'required|integer|exists:statuses,id',
            'tipo' => 'required|string|in:gramos,piezas',
        ]);

        $branchId = $request->branch_id;
        $statusId = $request->status_id;
        $tipo = $request->tipo;

        $branch = Branch::find($branchId);
        $status = Status::find($statusId);

        if ($tipo === 'gramos') {

            $rows = Product::query()
                ->select('lines.name as group_name',
                    DB::raw('SUM(products.weight) as total_grams'),
                    DB::raw('SUM(products.price) as total_money')
                )
                ->join('lines', 'lines.id', '=', 'products.line_id')
                ->where('products.branch_id', $branchId)
                ->where('products.status_id', $statusId)
                ->where('products.weight', '>', 0)
                ->groupBy('lines.name')
                ->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron productos de gramos con los filtros proporcionados.'
                ], 404);
            }

            $totalGrams = $rows->sum('total_grams');
            $totalMoney = $rows->sum('total_money');

            $reportData = compact(
                'tipo', 'branch', 'status',
                'rows', 'totalGrams', 'totalMoney'
            );

        } else { // piezas

            $rows = Product::query()
                ->select('categories.name as group_name',
                    DB::raw('COUNT(*) as pieces'),
                    DB::raw('SUM(products.price) as total_money')
                )
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->where('products.branch_id', $branchId)
                ->where('products.status_id', $statusId)
                ->where(function($q){
                    $q->whereNull('products.weight')->orWhere('products.weight', 0);
                })
                ->groupBy('categories.name')
                ->get();

            if ($rows->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron productos por piezas con los filtros proporcionados.'
                ], 404);
            }

            $totalPieces = $rows->sum('pieces');
            $totalMoney = $rows->sum('total_money');

            $reportData = compact(
                'tipo', 'branch', 'status',
                'rows', 'totalPieces', 'totalMoney'
            );
        }

        $date = Carbon::now()
            ->setTimezone('America/Mexico_City')
            ->format('Ymd_His');

        $branchIdentifier = str_replace(' ', '_', strtolower($branch->name));
        $filename = "inventario_sucursal_{$branchIdentifier}_{$date}.pdf";

        // Render vista PDF
        $reportData['date_now'] = Carbon::now()->setTimezone('America/Mexico_City');

        $pdf = PDF::loadView('reports.inventory', $reportData)
            ->setPaper('letter', 'portrait');

        return $pdf->download($filename);
    }
}