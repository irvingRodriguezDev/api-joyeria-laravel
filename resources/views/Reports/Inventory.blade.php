<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reporte Inventario</title>

    <style>
    @page {
        margin: 20mm;
    }

    body {
        font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
        font-size: 12px;
        color: #222;
        margin: 0;
        padding: 0;
    }

    .header {
        text-align: center;
        margin-bottom: 10px;
        position: fixed;
        top: -10px;
        left: 0;
        right: 0;
    }

    .company {
        font-size: 20px;
        font-weight: 700;
        color: #173757;
        text-transform: uppercase;
    }

    .meta {
        margin-top: 5px;
        font-size: 11px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        page-break-inside: auto;
    }

    .table th,
    .table td {
        border: 1px solid #b8b8b8;
        padding: 6px 8px;
        text-align: left;
    }

    .table th {
        background: #173757;
        color: white;
        text-transform: uppercase;
        font-size: 11px;
    }

    .right {
        text-align: right;
    }

    .totals {
        margin-top: 10px;
        font-weight: bold;
        color: #173757;
        font-size: 13px;
    }

    .small {
        font-size: 10px;
        color: #555;
    }

    tfoot {
        page-break-after: always;
    }

    .no-data {
        text-align: center;
        font-weight: bold;
        padding: 20px;
        color: #900;
    }

    .footer {
        position: fixed;
        bottom: -5px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 10px;
        color: #555;
    }

    .pagenum:before {
        content: counter(page);
    }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="company">Joyeria Luna</div>
        <div class="meta">
            Sucursal: {{ $branch->branch_name ?? 'N/A' }} |
            Tipo: {{ ucfirst($tipo) }} |
            Status: {{ $status->name ?? '' }} |
            Fecha: {{ $date_now->setTimezone('America/Mexico_City')->format('d/m/Y H:i') }} (CDMX)
        </div>
    </div>

    <main style="margin-top: 60px;">

        {{-- VALIDACIÓN SIN INFO --}}
        @if($rows->isEmpty())
        <div class="no-data">
            ❌ No se encontraron productos con los filtros seleccionados.
        </div>

        @else

        {{-- TABLA PARA GRAMOS --}}
        @if($tipo === 'gramos')
        <table class="table">
            <thead>
                <tr>
                    <th>Línea</th>
                    <th class="right">Total gramos</th>
                    <th class="right">Total dinero</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                <tr>
                    <td>{{ $r->group_name }}</td>
                    <td class="right">{{ number_format($r->total_grams, 2) }} g</td>
                    <td class="right">$ {{ number_format($r->total_money, 2, '.', ',') }} MXN</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            Total gramos: {{ number_format($total_grams ?? 0, 2) }} g <br>
            Total dinero: $ {{ number_format($totalMoney ?? 0, 2, '.', ',') }} MXN
        </div>

        {{-- TABLA PARA PIEZAS --}}
        @else
        <table class="table">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="right">Piezas</th>
                    <th class="right">Total dinero</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                <tr>
                    <td>{{ $r->group_name }}</td>
                    <td class="right">{{ number_format($r->pieces ?? 0) }}</td>
                    <td class="right">$ {{ number_format($r->total_money ?? 0, 2, '.', ',') }} MXN</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            Total piezas: {{ number_format($total_pieces ?? 0) }}<br>
            Total dinero: $ {{ number_format($totalMoney ?? 0, 2, '.', ',') }} MXN
        </div>

        @endif

        @endif

    </main>

    {{-- FOOTER PAGINADO --}}
    <div class="footer">
        Página <span class="pagenum"></span>
    </div>

</body>

</html>