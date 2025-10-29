<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #222;
    }

    .header {
        text-align: center;
        margin-bottom: 10px;
    }

    .title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 6px;
        color: #173757;
    }

    .meta {
        font-size: 11px;
        margin-bottom: 10px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
    }

    .table th,
    .table td {
        border: 1px solid #ccc;
        padding: 6px;
    }

    .table th {
        background: #173757;
        color: #fff;
        font-size: 11px;
    }

    .totals {
        margin-top: 12px;
        font-size: 13px;
        font-weight: bold;
    }

    .sale-block {
        margin-top: 12px;
        border: 1px solid #173757;
        padding: 6px;
        border-radius: 4px;
    }

    .items-title {
        font-weight: bold;
        margin-top: 6px;
        color: #173757;
    }

    .right {
        text-align: right;
    }

    .small {
        font-size: 11px;
    }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">REPORTE DE VENTAS</div>
        <div class="meta">
            Sucursal: {{ $branch->branch_name }} <br>
            Fechas: {{ $start }} — {{ $end }} <br>
            Generado: {{ $dateNowCDMX->format('d/m/Y H:i') }} (CDMX)
        </div>
    </div>

    @foreach($sales as $sale)
    <div class="sale-block">
        <strong>Venta #{{ $sale->id }}</strong><br>
        Cliente: {{ $sale->client->name ?? 'Público General' }} <br>
        Fecha: {{ $sale->created_at->timezone('America/Mexico_City')->format('d/m/Y H:i') }} <br>
        Métodos de pago:
        @foreach($sale->payments as $p)
        {{ ucfirst($p->payment_method) }} (${{ number_format($p->amount, 2, '.', ',') }})
        @endforeach
        <br>
        Total: ${{ number_format($sale->total, 2, '.', ',') }} MXN
        <br>
        Total pagado: ${{ number_format($sale->paid_out, 2, '.', ',') }} MXN

        <div class="items-title">Artículos vendidos:</div>
        <table class="table small">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="right">Categoria</th>
                    <th class="right">Linea</th>
                    <th class="right">Peso</th>
                    <th class="right">Precio final</th>
                    <!-- <th class="right">Subtotal</th> -->
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $d)
                <tr>
                    <td>{{ $d->product->clave ?? 'Producto' }} - {{ $d->product->description }}</td>
                    <td class="right">{{ $d->product->category->name }}</td>
                    <td class="right">{{ $d->product->line->name ?? 'N/A' }}</td>
                    <td class="right">{{ $d->product->weight ?? 'N/A' }}</td>
                    <td class="right">$ {{ number_format($d->final_price, 2, '.', ',') }}</td>
                    <!-- <td class="right">$ {{ number_format($d->price * $d->quantity, 2, '.', ',') }}</td> -->
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach


    <div class="totals">
        Total de ventas: {{ $countVentas }} <br>
        Artículos vendidos: {{ $totalArticulos }} <br>
        Total recaudado: ${{ number_format($totalVentas, 2, '.', ',') }} MXN
    </div>

</body>

</html>