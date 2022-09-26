<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntregaTurnosSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Sección la cual inserta en entrega_turnos_bitacora
        DB::insert('INSERT INTO entrega_turnos_bitacora (id_turno, id_movil, movil, placa, id_auxiliar, 
        id_conductor, id_medico, danos_automotor, comentarios_conductor, comentarios_auxiliar, comentarios_recibido)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        ['1', '1', 'ALSKSW', 'WPD198', '1', '1', '1', '0', 'Prueba1', 'Prueba2', 'Prueba Recibido']);

        //Sección en la cual se insertan datos de prueba en categorías de verificación
        DB::insert('INSERT INTO entrega_turnos_categoria_verificacion (categoria_verificacion) VALUES (?)',
        ['Prueba Verificación']);

        //Sección en la cual se insertan datos de prueba en categorías de selección/respuestas
        DB::insert('INSERT INTO entrega_turnos_categorias_selecciones (categoria_seleccion) VALUES (?)',
        ['Prueba selección']);

        //Sección en la cual se insertan datos de prueba en estados de verificación
        DB::insert('INSERT INTO entrega_turnos_verificacion_estado (estado_verificacion, id_categoria_respuesta,
        estado) VALUES (?, ?, ?)', ['Bueno', '1', '1']);

        //Sección en la cual inserta datos de prueba en tipos de verificación
        DB::insert('INSERT INTO entrega_turnos_verificacion_tipo (tipo_verificacion, id_categoria_verificacion,
        estado, es_automovil, es_ambulancia) VALUES (?, ?, ?, ?, ?)', ['Verificación 1', '1', '1', '1', '0']);

        //Sección en la cual inserta datos en la bitácora de verificaciones
        DB::insert('INSERT INTO entrega_turnos_verificacion_bitacora (id_bitacora, id_verificacion_tipo,
        id_estado_verificacion, comentarios, valor) VALUES (?, ?, ?, ?, ?)', ['1', '1', '1', 'Ninguno', '3000']);
    }
}
