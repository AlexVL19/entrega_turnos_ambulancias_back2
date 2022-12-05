<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntregaTurnosVerificacionEstadoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_verificacion_estado', function (Blueprint $table) {
            $table->id('id_verificacion');
            $table->string('estado_verificacion');
            $table->foreignId('id_categoria_respuesta')->references('id_seleccion')->on('entrega_turnos_categorias_selecciones')->onDelete('cascade');
            $table->tinyInteger('estado')->default(1);
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
        Schema::dropIfExists('entrega_turnos_verificacion_estado');
    }
}
