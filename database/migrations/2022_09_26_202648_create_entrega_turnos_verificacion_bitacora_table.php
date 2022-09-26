<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntregaTurnosVerificacionBitacoraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_verificacion_bitacora', function (Blueprint $table) {
            $table->id('id_verificacion_bitacora');
            $table->foreignId('id_bitacora')->references('id_bitacora')->on('entrega_turnos_bitacora')->onDelete('cascade');
            $table->foreignId('id_verificacion_tipo')->references('id_verificacion_tipo')->on('entrega_turnos_verificacion_tipo')->onDelete('cascade');
            $table->foreignId('id_estado_verificacion')->references('id_verificacion')->on('entrega_turnos_verificacion_estado')->onDelete('cascade');
            $table->string('comentarios')->nullable();
            $table->integer('valor')->nullable();
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
        Schema::dropIfExists('entrega_turnos_verificacion_bitacora');
    }
}
