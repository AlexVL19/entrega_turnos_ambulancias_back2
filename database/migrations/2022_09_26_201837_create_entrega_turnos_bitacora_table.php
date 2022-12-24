<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEntregaTurnosBitacoraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_bitacora', function (Blueprint $table) {
            $table->id('id_bitacora');
            $table->foreignId('id_turno')->references('Id_Hora')->on('htrabajadas')->onDelete('cascade');
            $table->foreignId('id_movil')->references('ID_Equipo')->on('equipos')->onDelete('cascade');
            $table->string('placa');
            $table->foreignId('id_auxiliar')->nullable()->references('Cod_Aux')->on('auxiliares')->onDelete('cascade');
            $table->foreignId('id_conductor')->nullable()->references('Cod_Con')->on('conductores')->onDelete('cascade');
            $table->tinyInteger('danos_automotor');
            $table->string('foto_automotor')->nullable();
            $table->text('comentarios_conductor')->nullable();
            $table->text('comentarios_auxiliar')->nullable();
            $table->tinyInteger('formulario_llenado')->default(1);
            $table->tinyInteger('novedades_formulario')->default(0);
            $table->text('comentarios_entregado')->nullable();
            $table->tinyInteger('aseo_terminal')->default(0);
            $table->tinyInteger('formulario_cargas_llenado')->default(0);
            $table->tinyInteger('formulario_temperatura_llenado')->default(0);
            $table->tinyInteger('estado_novedades')->default(0);
            $table->dateTime('fecha_registro')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entrega_turnos_bitacora');
    }
}
