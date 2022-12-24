<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEntregaTurnosNovedadesBitacoraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_novedades_bitacora', function (Blueprint $table) {
            $table->id('id_novedad');
            $table->foreignId('id_bitacora')->references('id_bitacora')->on('entrega_turnos_bitacora')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_turno')->references('Id_Hora')->on('htrabajadas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_movil')->references('ID_Equipo')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_auxiliar')->nullable()->references('Cod_Aux')->on('auxiliares')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_conductor')->nullable()->references('Cod_Con')->on('conductores')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_verificacion_tipo')->references('id_verificacion_tipo')->on('entrega_turnos_verificacion_tipo')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_categoria_verificacion')->references('id_categoria_verificacion')->on('entrega_turnos_categoria_verificacion')->onDelete('cascade')->onUpdate('cascade');
            $table->text('comentarios_novedad')->nullable();
            $table->tinyInteger('estado_revision')->default(0)->comment('0 si no ha sido revisado, 1 si está en revisión y 2 si ya ha sido revisado');
            $table->dateTime('fecha_creacion')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('fecha_revisando')->nullable();
            $table->dateTime('fecha_revision')->nullable();
            $table->text('nota_revision')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entrega_turnos_novedades_bitacora');
    }
}
