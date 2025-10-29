<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 10px;
        margin: 0;
        padding: 5px;
    }

    .center {
        text-align: center;
    }

    .line {
        border-top: 1px dashed #000;
        margin: 4px 0;
    }

    table {
        width: 100%;
    }

    .right {
        text-align: right;
    }

    .bold {
        font-weight: bold;
    }
    </style>
</head>

<body>

    <div class="center bold">JOYERÍA LUNA</div>
    <div class="center">CORTE DIARIO</div>
    <div class="center">Sucursal: {{ $branch->branch_name ?? 'N/A' }}</div>
    <div class="center">{{ $date->format('d/m/Y H:i') }} CDMX</div>

    <div class="line"></div>
    <table>
        <tr>
            <td>Ventas:</td>
            <td class="right">{{ $salesCount }}</td>
        </tr>
        <tr>
            <td>Artículos vendidos:</td>
            <td class="right">{{ $totalItems }}</td>
        </tr>
    </table>
    <div class="line"></div>

    <table>
        <tr>
            <td>Efectivo:</td>
            <td class="right">${{ number_format($payments['cash'], 2) }}</td>
        </tr>
        <tr>
            <td>Tarjeta:</td>
            <td class="right">${{ number_format($payments['card'], 2) }}</td>
        </tr>
        <tr>
            <td>Transferencia:</td>
            <td class="right">${{ number_format($payments['transfer'], 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>
    <div class="bold">Total Recaudado</div>
    <div class="bold right">${{ number_format($totalCollected, 2) }}</div>
    <div class="line"></div>

    <div class="center">Gracias por su preferencia</div>

</body>

</html>