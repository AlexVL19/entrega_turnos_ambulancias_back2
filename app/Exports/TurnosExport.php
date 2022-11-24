<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TurnosExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles {
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
            "Turno",
            "Móvil",
            "Placa",
            "Auxiliar",
            "Conductor",
            "Comentarios del conductor",
            "Comentarios del auxiliar",
            "Comentarios al entregar",
            "Novedades",
            "Fecha de registro",
        ];
    }

    public function styles(Worksheet $sheet) {
        return [
            
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
