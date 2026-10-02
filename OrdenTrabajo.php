<?php

declare(strict_types=1);

// ENUMERACIÓN DE TIPOS DE SERVICIO


enum ServicioReparacion: string
{
    case PANTALLA = 'pantalla';
    case BATERIA = 'bateria';
    case PLACA_BASE = 'placa_base';

    public function descripcion(): string
    {
        return match ($this) {
            self::PANTALLA => 'Reparación o sustitución de pantalla',
            self::BATERIA => 'Sustitución de batería',
            self::PLACA_BASE => 'Reparación de placa base',
        };
    }
}

// CLASE INMUTABLE DE ORDEN DE TRABAJO


final class OrdenTrabajo
{
    public function __construct(
        public readonly int $id,
        public readonly string $cliente,
        public readonly ServicioReparacion $servicio,
        public readonly float $coste
    ) {
    }
}


// CÁLCULO DEL PRESUPUESTO

function calcularPresupuesto(
    float $manoObra,
    float $recambios
): float {

    $subtotal = $manoObra + $recambios;

    $iva = $subtotal * 0.21;

    return $subtotal + $iva;
}