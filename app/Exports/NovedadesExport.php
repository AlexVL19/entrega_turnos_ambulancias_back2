<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class NovedadesExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles {
    protected $datos_tabla;


    /* Se instancia un constructor para que reciba un array de datos generado por un controlador o un 
    modelo. */
    public function __construct(array $datos) {
        $this->datos_tabla = $datos;
    }

    /* Se crea el documento con base a los datos ya recibidos */
    public function array(): array
    {
        return $this->datos_tabla;
    }

    /* Se crea un array de encabezados dentro de esta función */
    public function headings(): array
    {
        return [
            "Móvil",
            "Turno",
            "Fecha de creación",
            "Auxiliar",
            "Conductor",
            "Chequeo en el cual está la novedad",
            "Categoría del chequeo",
            "Comentario de la novedad",
            "Estado de revisión",
            "Fecha de última revisión",
            "Nota de revisión"
        ];
    }

    /* Se crea un array de estilos que permiten decorar el documento que ya hemos creado */
    public function styles(Worksheet $sheet) {
        return [
            
            /* Para la fila 1 del documento, el texto estará en negrita, el color de las celdas será gris,
            y los bordes rodearán toda la fila. */
            1 => [
                'font' => [
                    'bold' => true
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'argb' => 'E1E1E1'
                    ]
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ],

                'borders' => [
                    'outline' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]
        ];
    }
}