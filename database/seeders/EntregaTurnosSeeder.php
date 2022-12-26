<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntregaTurnosSeeder extends Seeder
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
            'entrega_turnos_categorias_selecciones' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'entrega_turnos_categorias_selecciones.csv',
                'columns' => 'id_seleccion, categoria_seleccion'
            ],

            'entrega_turnos_categoria_verificacion' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'entrega_turnos_categoria_verificacion.csv',
                'columns' => 'id_categoria_verificacion, categoria_verificacion'
            ],

            'entrega_turnos_tipos_productos_aseo' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'entrega_turnos_tipos_productos_aseo.csv',
                'columns' => 'id_tipo_producto, tipo_producto'
            ],

            'entrega_turnos_productos_aseo' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'entrega_turnos_productos_aseo.csv',
                'columns' => 'id_producto_aseo, producto, tipo_producto, estado'
            ],

            'entrega_turnos_verificacion_estado' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'entrega_turnos_verificacion_estado.csv',
                'columns' => 'id_verificacion, estado_verificacion, id_categoria_respuesta, estado'
            ],

            'entrega_turnos_verificacion_tipo' => [
                'file' => 'csv' . DIRECTORY_SEPARATOR . 'entrega_turnos_verificacion_tipo.csv',
                'columns' => 'id_verificacion_tipo, tipo_verificacion, id_categoria_verificacion, estado, tipo_movil, tiene_carga, requiere_valores'
            ]
        ];


        /* Luego, se recorre cada tabla y entabla una conexión de MySQL que indique que, los datos del 
        archivo especificado en esa iteración se van a insertar en la tabla correspondiente. Cabe 
        destacar que también se establecen parámetros adicionales al leer para que se ajuste a la sintaxis
        que ofrecen los .csv */
        foreach ($tables as $key => $table) {
            DB::connection()->getpdo()->exec("LOAD DATA LOCAL INFILE '" . str_replace(DIRECTORY_SEPARATOR, '/', public_path($prefix . DIRECTORY_SEPARATOR . "{$key}.{$prefix}" . "'" . " INTO TABLE " . $key  . " FIELDS TERMINATED BY ',' ENCLOSED BY '" . '"' . "' LINES TERMINATED BY '\r\n' IGNORE 1 LINES")));
        }

        $query_config = "INSERT INTO configs (`key`, `value`) VALUES (?, ?)";

        $result_config = DB::connection()->select(DB::raw($query_config), [
            'entrega_turnos_carga_equipos',
            '80'
        ]);
    }
}
