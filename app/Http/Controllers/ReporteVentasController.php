<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Branch;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ReporteVentasController extends Controller
{
    public function ventasPorRango(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'startDate' => 'required|date',
            'endDate'   => 'required|date'
        ]);

        $branchId = $request->branch_id;

        // Fechas con zona horaria CDMX
        $startDate = Carbon::parse($request->startDate, 'America/Mexico_City')
            ->startOfDay()
            ->setTimezone('UTC');

        $endDate = Carbon::parse($request->endDate, 'America/Mexico_City')
            ->endOfDay()
            ->setTimezone('UTC');

        // Query principal
        $salesQuery = Sale::with(['client', 'branch', 'details.product','details.product.category','details.product.line', 'payments'])
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        if (Schema::hasColumn('sales', 'canceled')) {
            $salesQuery->where('canceled', 0);
        } elseif (Schema::hasColumn('sales', 'status')) {
            $salesQuery->where('status', '!=', 'canceled');
        }

        $sales = $salesQuery->orderBy('created_at', 'asc')->get();

        if ($sales->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron ventas en el rango seleccionado.'
            ], 404);
        }
        // return $sales;
        $totalVentas = $sales->sum('paid_out');
        $countVentas = $sales->count();
        $totalArticulos = $sales->flatMap->saleDetails->sum('quantity');

        $branch = $sales->first()->branch;
        $dateNowCDMX = Carbon::now('America/Mexico_City');
        // return $sales;
        $pdf = Pdf::loadView('pdf.reporte_ventas', [
            'sales' => $sales,
            'branch' => $branch,
            'totalVentas' => $totalVentas,
            'countVentas' => $countVentas,
            'totalArticulos' => $totalArticulos,
            'dateNowCDMX' => $dateNowCDMX,
            'start' => $request->startDate,
            'end' => $request->endDate
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("reporte_ventas.pdf");
    }
}