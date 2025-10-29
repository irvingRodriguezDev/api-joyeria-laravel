<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <title>Corte de Caja - Rango</title>
    <style>
    @page {
        margin: 20mm;
    }

    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 12px;
        color: #222;
    }

    .header {
        text-align: center;
        margin-bottom: 8px;
    }

    .company {
        font-size: 18px;
        font-weight: 700;
        color: #173757;
        text-transform: uppercase;
    }

    .meta {
        font-size: 11px;
        color: #555;
        margin-bottom: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
        font-size: 11px;
    }

    th {
        background: #173757;
        color: #fff;
        text-transform: uppercase;
        font-size: 11px;
    }

    .right {
        text-align: right;
    }

    .totals {
        margin-top: 12px;
        font-weight: bold;
        color: #173757;
    }

    .small {
        font-size: 10px;
        color: #666;
    }

    .section {
        margin-top: 10px;
    }

    .footer {
        position: fixed;
        bottom: 10px;
        left: 20mm;
        right: 20mm;
        text-align: center;
        font-size: 10px;
        color: #666;
    }
    </style>
</head>

<body>
    <div class="header">
        <div class="company">Joyeria Luna</div>
        <div class="meta">
            Sucursal: {{ $branch->branch_name ?? 'N/A' }} |
            Corte del: {{ $start->format('d/m/Y H:i') }} |
            al: {{ $end->format('d/m/Y H:i') }} (CDMX)
        </div>
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="right">Total (MXN)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Efectivo</td>
                    <td class="right">$ {{ number_format($payments['cash'] ?? 0, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td>Tarjeta</td>
                    <td class="right">$ {{ number_format($payments['card'] ?? 0, 2, '.', ',') }}</td>
                </tr>
                <tr>
                    <td>Transferencia</td>
                    <td class="right">$ {{ number_format($payments['transfer'] ?? 0, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section totals">
        <div>Total recaudado: $ {{ number_format($total_collected ?? 0, 2, '.', ',') }} MXN</div>
        <div>Total registrado en sales.total: $ {{ number_format($total_sales_table ?? 0, 2, '.', ',') }} MXN</div>
        @if(isset($total_paid_table))
        <div>Total registrado en sales.total_paid: $ {{ number_format($total_paid_table ?? 0, 2, '.', ',') }} MXN</div>
        @endif
        <div>Ventas: {{ $sales_count }}</div>
        <div>Items vendidos: {{ $total_items }}</div>
    </div>

    <div class="footer small">
        Generado: {{ $date_now->format('d/m/Y H:i') }} (CDMX) — Joyeria Luna
    </div>
</body>

</html>