<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Salida de Productos</title>
    <style>
    body {
        font-family: sans-serif;
        font-size: 12px;
    }

    .title {
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 6px;
        text-align: left;
    }

    th {
        background: #eee;
    }

    .info-table {
        margin-top: 15px;
    }
    </style>
</head>

<body>

    <div class="title">Salida de Productos</div>

    <table class="info-table">
        <tr>
            <th>Folio</th>
            <td>{{ $departure->id }}</td>
        </tr>
        <tr>
            <th>Autoriza</th>
            <td>{{ $departure->auth }}</td>
        </tr>
        <tr>
            <th>Recibe</th>
            <td>{{ $departure->recibe }}</td>
        </tr>
        <tr>
            <th>Sucursal</th>
            <td>{{ $departure->branch->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Motivo</th>
            <td>{{ $departure->description }}</td>
        </tr>
        <tr>
            <th>Fecha</th>
            <td>{{ $departure->created_at->format('d/m/Y') }}</td>
        </tr>
    </table>

    <br>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Clave</th>
                <th>Descripcion</th>
                <th>Linea</th>
                <th>Categoria</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($departure->details as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->clave }}</td>
                <td>{{ $item->product->description }}</td>
                <td>{{ $item->product->line->name ?? 'N/A' }}</td>
                <td>{{ $item->product->category->name }}</td>
                <td>{{ $item->product->price }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>