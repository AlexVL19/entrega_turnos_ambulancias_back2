<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovilSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prefix = 'csv'; //Prefijo de los archivos que se van a usar en esta seeder

        /* Array de tablas las cuales se van a llenar, viene con su respectivo archivo del cual se va a leer
        y las columnas de las cuales se van a insertar. Se pueden poner cuantas tablas se prefiera */
        $tables = [
            'movil_cambios_aceite_hidraulico' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'movil_cambios_aceite_hidraulico.csv',
                'columns' => 'id_cambio_aceite_hidraulico, id_equipo, fecha_ultimo_cambio'
            ],

            'movil_cambios_aceite_motor' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'movil_cambios_aceite_motor.csv',
                'columns' => 'id_cambio_aceite_motor, id_equipo, fecha_ultimo_cambio'
            ],

            'movil_cambios_frenos' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'movil_cambios_frenos.csv',
                'columns' => 'id_cambio_frenos, id_equipo, fecha_ultimo_cambio'
            ],

            'movil_cambios_suspension' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'movil_cambios_suspension.csv',
                'columns' => 'id_cambio_suspension, id_equipo, fecha_ultimo_cambio'
            ],

            'movil_extintores' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'movil_extintores.csv',
                'columns' => 'id_extintor, id_equipo, fecha_expedicion, fecha_vencimiento'
            ],

            'movil_soat' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'movil_soat.csv',
                'columns' => 'id_soat, id_equipo, fecha_expedicion, fecha_vencimiento'
            ],

            'movil_tecnomecanica' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'movil_tecnomecanica.csv',
                'columns' => 'id_tecnomecanica, id_equipo, fecha_revision'
            ],
        ];


        /* Luego, se recorre cada tabla y entabla una conexión de MySQL que indique que, los datos del 
        archivo especificado en esa iteración se van a insertar en la tabla correspondiente. Cabe 
        destacar que también se establecen parámetros adicionales al leer para que se ajuste a la sintaxis
        que ofrecen los .csv */
        foreach ($tables as $key => $table) {
            DB::connection()->getpdo()->exec("LOAD DATA LOCAL INFILE '" . str_replace(DIRECTORY_SEPARATOR, '/', public_path($prefix . DIRECTORY_SEPARATOR . "{$key}.{$prefix}" . "'" . " INTO TABLE " . $key  . " FIELDS TERMINATED BY ',' ENCLOSED BY '" . '"' . "' LINES TERMINATED BY '\r\n' IGNORE 1 LINES")));
        }
    }
}
