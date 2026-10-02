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