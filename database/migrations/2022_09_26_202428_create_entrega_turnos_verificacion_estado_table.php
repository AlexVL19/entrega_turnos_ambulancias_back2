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
            $table->unsignedBigInteger('id_categoria_respuesta');
            $table->foreign('id_categoria_respuesta', 'cat_resp_foreign')->references('id_seleccion')->on('entrega_turnos_categorias_selecciones')->onDelete('cascade')->onUpdate('cascade');
            $table->tinyInteger('estado')->default(1);
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
