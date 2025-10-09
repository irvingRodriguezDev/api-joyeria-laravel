<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ticket de venta #{{ $sale->id }}</title>
    <style>
    * {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 12px;
        box-sizing: border-box;
    }

    body {
        width: 58mm;
        margin: 0;
        padding: 4px;
        color: #000;
    }

    .center {
        text-align: center;
    }

    .right {
        text-align: right;
    }

    .bold {
        font-weight: bold;
    }

    hr {
        border: none;
        border-top: 1px dashed #999;
        margin: 5px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        text-align: left;
        border-bottom: 1px solid #ccc;
        font-size: 11px;
        padding-bottom: 3px;
    }

    td {
        padding: 2px 0;
        vertical-align: top;
    }

    .total-box {
        text-align: center;
        margin: 10px 0;
        padding: 6px 0;
        border: 1px solid #000;
        font-size: 16px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .footer {
        margin-top: 8px;
        text-align: center;
        font-size: 11px;
    }

    .ticket-title {
        font-size: 14px;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <div class="center">
        <div class="ticket-title">{{ strtoupper($sale->branch->branch_name ?? 'MI NEGOCIO') }}</div>
        @if($sale->branch->address)
        <div>{{ $sale->branch->address }}</div>
        @endif
        <div>Ticket de Venta #{{ $sale->id }}</div>
        <div>{{ $sale->created_at->format('d/m/Y H:i') }}</div>
    </div>

    <hr>

    <p><strong>Cliente:</strong> {{ $sale->client->name ?? 'Público General' }} {{$sale->client->lastname}}</p>

    <table>
        <thead>
            <tr>
                <th>Prod</th>
                <th>Grs</th>
                <th class="right">Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $detail)
            <tr>
                <td>{{ $detail->product->clave ?? 'Producto' }}</td>
                <td>{{ $detail->product->weight ?? '' }}</td>
                <td class="right">${{ number_format($detail->final_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        TOTAL<br>${{ number_format($sale->total, 2, '.', ',') }}
    </div>

    @if(isset($sale->payments) && count($sale->payments) > 0)
    <hr>
    <p><strong>Pagos recibidos:</strong></p>
    @php
    $totalPagado = 0;
    @endphp

    @foreach($sale->payments as $payment)
    @php
    $method = strtolower($payment->payment_method);
    switch ($method) {
    case 'cash':
    $methodLabel = 'Efect.';
    break;
    case 'card':
    $methodLabel = 'Tarj.';
    break;
    default:
    $methodLabel = ucfirst($method);
    break;
    }
    $totalPagado += $payment->amount;
    @endphp
    <div>
        - {{ $methodLabel }}:
        ${{ number_format($payment->amount, 2, '.', ',') }}
        @if(!empty($payment->reference))
        <small>({{ $payment->reference }})</small>
        @endif
    </div>
    @endforeach

    @php
    $restante = round($sale->total - $totalPagado, 2);
    @endphp

    <hr>
    <table style="width: 100%; font-size: 12px;">
        <tr>
            <td><strong>Total Pagado:</strong></td>
            <td class="right">${{ number_format($totalPagado, 2, '.', ',') }}</td>
        </tr>
        <tr>
            <td><strong>Restante:</strong></td>
            <td class="right">
                @if($restante > 0)
                ${{ number_format($restante, 2, '.', ',') }}
                @else
                <span style="font-weight: bold; color: #0a0;">✓ Pago completo</span>
                @endif
            </td>
        </tr>
    </table>
    @endif

    <hr>
    <div class="footer">
        <p>¡Gracias por su compra!</p>
        <p>Vuelva pronto</p>
    </div>
</body>

</html>