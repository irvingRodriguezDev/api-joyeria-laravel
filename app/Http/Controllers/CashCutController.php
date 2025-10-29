<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use PDF;
use App\Models\Sale;
use App\Models\Branch;

class CashCutController extends Controller
{
    /**
     * Corte por rango de fechas -> genera PDF descargable.
     * Request: branch_id, startDate (Y-m-d), endDate (Y-m-d)
     */
    public function rangePdf(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
        ]);

        $branchId = $request->branch_id;
        $startDateInput = $request->startDate;
        $endDateInput = $request->endDate;

        // Forzar zona horaria CDMX y luego convertir a UTC para comparar con DB timestamps en UTC
        $start = Carbon::parse($startDateInput, 'America/Mexico_City')->startOfDay()->setTimezone('UTC');
        $end = Carbon::parse($endDateInput, 'America/Mexico_City')->endOfDay()->setTimezone('UTC');

        // Base query de ventas para el rango y sucursal
        $salesQuery = Sale::query()->where('branch_id', $branchId)
            ->whereBetween('created_at', [$start, $end]);

        // Excluir ventas canceladas si la columna existe
        if (Schema::hasColumn('sales', 'canceled')) {
            $salesQuery->where('canceled', 0);
        } elseif (Schema::hasColumn('sales', 'status')) {
            $salesQuery->where('status', '!=', 'canceled');
        }

        $saleIds = $salesQuery->pluck('id')->toArray();

        if (empty($saleIds)) {
            return response()->json([
                'message' => 'No se encontraron ventas en el rango indicado para la sucursal seleccionada.'
            ], 404);
        }

        // Totales por método de pago (payments table)
        $paymentsByMethod = DB::table('payments')
            ->select('payment_method as method', DB::raw('SUM(amount) as total'))
            ->whereIn('sale_id', $saleIds)
            ->groupBy('method')
            ->get()
            ->keyBy('method');

        // Asegurar que existan las 3 metodologias, si no, poner 0
        $methods = ['cash', 'card', 'transfer'];
        $payments = [];
        foreach ($methods as $m) {
            $payments[$m] = isset($paymentsByMethod[$m]) ? (float) $paymentsByMethod[$m]->total : 0.0;
        }

        // Total recolectado (por pagos)
        $totalCollected = array_sum($payments);

        // Total "registrado" en sales (por si quieres comparar)
        $totalSalesTable = $salesQuery->sum('total'); // campo 'total' asumido
        $totalPaidTable = $salesQuery->sum('paid_out'); // si existe

        // Cantidad de ventas
        $salesCount = count($saleIds);

        // Total items (sale_details.quantity)
        $totalItems = DB::table('sale_details')
            ->whereIn('sale_id', $saleIds)
            ->sum('quantity');

        // Branch info
        $branch = Branch::find($branchId);

        // Preparar datos para vista PDF
        $dateNowCDMX = Carbon::now('America/Mexico_City');

        $reportData = [
            'branch' => $branch,
            'start' => $start->setTimezone('America/Mexico_City'), // para mostrar en CDMX
            'end' => $end->setTimezone('America/Mexico_City'),
            'payments' => $payments,
            'total_collected' => $totalCollected,
            'total_sales_table' => $totalSalesTable,
            'total_paid_table' => $totalPaidTable,
            'sales_count' => $salesCount,
            'total_items' => (int)$totalItems,
            'date_now' => $dateNowCDMX,
        ];

        // Nombre de archivo
        $dateForFilename = $dateNowCDMX->format('Ymd_His');
        $branchIdentifier = $branch ? preg_replace('/\s+/', '_', strtolower($branch->name ?? "branch_{$branchId}")) : "branch_{$branchId}";
        $filename = "corte_caja_{$branchIdentifier}_{$dateForFilename}.pdf";

        // Render view (resources/views/pdf/cashcut-range.blade.php)
        $pdf = PDF::loadView('pdf.cashcut-range', $reportData)
            ->setPaper('letter', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Corte diario -> ticket en texto plano (descarga .txt)
     * Request: branch_id
     */
    public function dailyTicket(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
        ]);
    
        $branchId = $request->branch_id;
    
        $todayStart = Carbon::now('America/Mexico_City')->startOfDay()->setTimezone('UTC');
        $todayEnd = Carbon::now('America/Mexico_City')->endOfDay()->setTimezone('UTC');
    
        $salesQuery = Sale::query()->where('branch_id', $branchId)
            ->whereBetween('created_at', [$todayStart, $todayEnd]);
    
        if (Schema::hasColumn('sales', 'canceled')) {
            $salesQuery->where('canceled', 0);
        } elseif (Schema::hasColumn('sales', 'status')) {
            $salesQuery->where('status', '!=', 'canceled');
        }
    
        $saleIds = $salesQuery->pluck('id')->toArray();
    
        if (empty($saleIds)) {
            return response()->json([
                'message' => 'No se encontraron ventas para hoy en la sucursal seleccionada.'
            ], 404);
        }
    
        $paymentsByMethod = DB::table('payments')
            ->select('payment_method as method', DB::raw('SUM(amount) as total'))
            ->whereIn('sale_id', $saleIds)
            ->groupBy('method')
            ->get()
            ->keyBy('method');
    
        $methods = ['cash', 'card', 'transfer'];
        $payments = [];
        foreach ($methods as $m) {
            $payments[$m] = isset($paymentsByMethod[$m]) ? (float) $paymentsByMethod[$m]->total : 0.0;
        }
    
        $totalCollected = array_sum($payments);
        $salesCount = count($saleIds);
        $totalItems = DB::table('sale_details')->whereIn('sale_id', $saleIds)->sum('quantity');
    
        $branch = Branch::find($branchId);
        $dateNowCDMX = Carbon::now('America/Mexico_City');
        $dateForFilename = $dateNowCDMX->format('Ymd_His');
        $branchIdentifier = $branch ? preg_replace('/\s+/', '_', strtolower($branch->branch_name ?? "branch_{$branchId}")) : "branch_{$branchId}";
        $filename = "ticket_corte_{$branchIdentifier}_{$dateForFilename}.pdf";
    
        $data = [
            'branch' => $branch,
            'date' => $dateNowCDMX,
            'salesCount' => $salesCount,
            'totalItems' => $totalItems,
            'payments' => $payments,
            'totalCollected' => $totalCollected
        ];
    
        $pdf = \PDF::loadView('pdf.corte-ticket', $data)
            ->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm
    
        return $pdf->download($filename);
    }

}