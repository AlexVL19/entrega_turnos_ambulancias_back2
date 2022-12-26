<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEntregaTurnosControlTemperaturaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_control_temperatura', function (Blueprint $table) {
            $table->id('id_control_temperatura');
            $table->unsignedInteger('id_movil')->length(10);
            $table->foreign('id_movil')->references('ID_Equipo')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_bitacora')->references('id_bitacora')->on('entrega_turnos_bitacora')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('temperatura_normal');
            $table->integer('temperatura_max');
            $table->integer('temperatura_min');
            $table->integer('humedad');
            $table->tinyInteger('jornada');
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
        Schema::dropIfExists('entrega_turnos_control_temperatura');
    }
}
