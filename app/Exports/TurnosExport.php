<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TurnosExport implements FromArray, WithHeadings
{
    protected $datos_tabla;

    public function __construct(array $datos) {
        $this->datos_tabla = $datos;
    }

    public function array(): array
    {
        return $this->datos_tabla;
    }

    public function headings(): array
    {
        return [
            "Número de bitácora",
            "Turno",
            "Móvil",
            "Placa",
            "Auxiliar",
            "Conductor",
            "Daños?",
            "Fotos del automotor",
            "Comentarios del conductor",
            "Comentarios del auxiliar",
            "Formulario entregado",
            "Novedades",
            "Comentarios al entregar",
            "Fecha de registro",
            "Aseo registrado",
            "Cargas finales registradas",
            "Temperatura y humedad registradas",
            "Estado de las novedades"
        ];
    }
}
