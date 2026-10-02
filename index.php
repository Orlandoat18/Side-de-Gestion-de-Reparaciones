<?php

require_once 'config.php';
require_once 'OrdenTrabajo.php';


// BLOQUE 1 - VALIDACIÓN DE LA PETICIÓN

// Recogemos el número de solicitud desde la URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Debe ser un entero mayor que 0
if ($id === false || $id === null || $id <= 0) {
    http_response_code(400);
    exit('Error 400: el número de solicitud debe ser un entero positivo.');
}

// Si no existe cliente, usamos un valor por defecto
$cliente = $_GET['cliente'] ?? 'Cliente Anónimo';

// Convertimos a mayúsculas teniendo en cuenta caracteres especiales
$cliente = mb_strtoupper($cliente, 'UTF-8');

// Calculamos la longitud correctamente
$longitudCliente = mb_strlen($cliente, 'UTF-8');





// BLOQUE 2 - ORDEN DE TRABAJO


// Creamos una orden de reparación
$orden = new OrdenTrabajo(
    id: $id,
    cliente: $cliente,
    servicio: ServicioReparacion::PANTALLA,
    coste: 150.00
);



//Cálculo del presupuesto


$manoObra = 50.00;
$recambiosCoste = 100.00;

// Argumentos nombrados
$presupuesto = calcularPresupuesto(
    manoObra: $manoObra,
    recambios: $recambiosCoste
);


// Catálogo de cambios

$recambios = [
    ['nombre' => 'Pantalla', 'precio' => 120.00, 'stock' => 5],
    
    ['nombre' => 'Batería','precio' => 60.00,'stock' => 0],

    ['nombre' => 'Placa Base','precio' => 200.00,'stock' => 2],
    
    ['nombre' => 'Cámara','precio' => 80.00,'stock' => 4],
    
    ['nombre' => 'Conector USB','precio' => 35.00,'stock' => 0],
    
    ['nombre' => 'Altavoz','precio' => 45.00,'stock' => 3],
    
    ['nombre' => 'Micrófono','precio' => 30.00,'stock' => 6]];


// Recargo de almacenamiento
$recargo = 0.10;

// Modificamos directamente los elementos originales
foreach ($recambios as &$recambio) {
    $recambio['precio'] += $recambio['precio'] * $recargo;
}

// Eliminamos explícitamente la referencia
unset($recambio);



// Filtrado del stock
$recambiosDisponibles = array_filter(
    $recambios,
    function ($recambio) { return $recambio['stock'] > 0;
    }
);



//Valor total del catálogo


$valorCatalogo = array_reduce(
    $recambiosDisponibles,
    function ($total, $recambio) {
        return $total + ($recambio['precio'] * $recambio['stock']);
    },
    0);


// BLOQUE 3 - PAGINACIÓN


$elementosPorPagina = 3;

$totalElementos = count($recambiosDisponibles);

$totalPaginas = max(
    1,
    (int) ceil($totalElementos / $elementosPorPagina)
);

// Página solicitada
$pagina = filter_input(
    INPUT_GET,
    'page',
    FILTER_VALIDATE_INT
);

// Si no existe o es menor que 1
if ($pagina === false || $pagina === null) {
    $pagina = 1;
}

// Limitamos la página entre 1 y el máximo
$pagina = max(1, min($pagina, $totalPaginas));

// Calculamos desde qué elemento empezar
$inicio = ($pagina - 1) * $elementosPorPagina;

// Obtenemos los elementos de la página
$recambiosPagina = array_slice(
    array_values($recambiosDisponibles),
    $inicio,
    $elementosPorPagina
);



// BLOQUE 4 - BÚFER DE SALIDA


ob_start();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TechFix - Presupuesto</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        .datos {
            background: #f2f2f2;
            padding: 20px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #333;
            color: white;
        }

        .total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }

        .paginacion {
            margin-top: 20px;
        }

        .paginacion a {
            margin-right: 10px;
        }

    </style>

</head>

<body>

    <h1>TechFix - Gestión de Reparaciones</h1>


    <div class="datos">

        <h2>Datos de la solicitud</h2>

        <p>
            <strong>Número de solicitud:</strong>
            <?= htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p>
            <strong>Cliente:</strong>
            <?= htmlspecialchars($cliente, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <p>
            <strong>Longitud del nombre:</strong>
            <?= htmlspecialchars((string) $longitudCliente, ENT_QUOTES, 'UTF-8') ?>
            caracteres
        </p>

    </div>


    <div class="datos">

        <h2>Presupuesto</h2>

        <p>
            <strong>Mano de obra:</strong>
            <?= number_format($manoObra, 2, ',', '.') ?> €
        </p>

        <p>
            <strong>Recambios:</strong>
            <?= number_format($recambiosCoste, 2, ',', '.') ?> €
        </p>

        <p>
            <strong>Total con IVA:</strong>
            <?= number_format($presupuesto, 2, ',', '.') ?> €
        </p>

    </div>


    <h2>Recambios disponibles</h2>

    <table>

        <thead>

            <tr>
                <th>Recambio</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Valor</th>
            </tr>

        </thead>

        <tbody>

        <?php if (empty($recambiosPagina)): ?>

            <tr>

                <td colspan="4">
                    No hay recambios disponibles.
                </td>

            </tr>

        <?php else: ?>

            <?php foreach ($recambiosPagina as $recambio): ?>

                <tr>

                    <td>
                        <?= htmlspecialchars(
                            $recambio['nombre'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </td>

                    <td>
                        <?= number_format(
                            $recambio['precio'],
                            2,
                            ',',
                            '.'
                        ) ?> €
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            (string) $recambio['stock'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </td>

                    <td>
                        <?= number_format(
                            $recambio['precio'] * $recambio['stock'],
                            2,
                            ',',
                            '.'
                        ) ?> €
                    </td>

                </tr>

            <?php endforeach; ?>

        <?php endif; ?>

        </tbody>

    </table>


    <div class="total">

        Valor total del catálogo:
        <?= number_format($valorCatalogo, 2, ',', '.') ?> €

    </div>


    <div class="paginacion">

        <strong>Página <?= $pagina ?> de <?= $totalPaginas ?></strong>

        <br><br>

        <?php if ($pagina > 1): ?>

            <a href="?id=<?= urlencode((string) $id) ?>&cliente=<?= urlencode($cliente) ?>&page=<?= $pagina - 1 ?>">
                ← Anterior
            </a>

        <?php endif; ?>


        <?php if ($pagina < $totalPaginas): ?>

            <a href="?id=<?= urlencode((string) $id) ?>&cliente=<?= urlencode($cliente) ?>&page=<?= $pagina + 1 ?>">
                Siguiente →
            </a>

        <?php endif; ?>

    </div>

</body>

</html>

<?php


// ENVIAR EL CONTENIDO DEL BÚFER


$html = ob_get_clean();

echo $html;