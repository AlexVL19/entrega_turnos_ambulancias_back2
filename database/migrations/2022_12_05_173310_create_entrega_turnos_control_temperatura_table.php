<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('id_movil')->references('ID_Equipo')->on('equipos')->onDelete('cascade');
            $table->foreignId('id_bitacora')->references('id_bitacora')->on('entrega_turnos_bitacora')->onDelete('cascade');
            $table->integer('temperatura_max');
            $table->integer('temperatura_min');
            $table->integer('humedad_max');
            $table->integer('humedad_min');
            $table->tinyInteger('jornada');
            $table->timestamps();
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
