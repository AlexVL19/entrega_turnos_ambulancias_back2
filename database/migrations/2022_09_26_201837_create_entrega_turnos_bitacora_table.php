<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->foreignId('id_turno')->references('IdTurno')->on('servicio_turnos_aperturas')->onDelete('cascade');
            $table->foreignId('id_movil')->references('VEHCOD')->on('equipos')->onDelete('cascade');
            $table->string('movil');
            $table->string('placa');
            $table->foreignId('id_auxiliar')->references('Cod_Aux')->on('auxiliares')->onDelete('cascade');
            $table->foreignId('id_conductor')->references('Cod_Con')->on('conductores')->onDelete('cascade');
            $table->foreignId('id_medico')->references('Cod_Med')->on('medicos')->onDelete('cascade');
            $table->tinyInteger('danos_automotor');
            $table->string('foto_automotor')->nullable();
            $table->string('comentarios_conductor')->nullable();
            $table->string('comentarios_auxiliar')->nullable();
            $table->string('comentarios_recibido')->nullable();
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
        Schema::dropIfExists('entrega_turnos_bitacora');
    }
}
