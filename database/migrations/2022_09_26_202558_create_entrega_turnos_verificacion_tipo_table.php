<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntregaTurnosVerificacionTipoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_verificacion_tipo', function (Blueprint $table) {
            $table->id('id_verificacion_tipo');
            $table->string('tipo_verificacion');
            $table->foreignId('id_categoria_verificacion')->references('id_categoria_verificacion')->on('entrega_turnos_categoria_verificacion')->onDelete('cascade');
            $table->tinyInteger('estado');
            $table->tinyInteger('es_automovil');
            $table->tinyInteger('es_ambulancia');
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
        Schema::dropIfExists('entrega_turnos_verificacion_tipo');
    }
}
