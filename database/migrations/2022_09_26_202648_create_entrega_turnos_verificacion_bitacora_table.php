<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->foreignId('id_categoria_verificacion')->references('id_categoria_verificacion')->on('entrega_turnos_categoria_verificacion')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_estado_verificacion')->references('id_verificacion')->on('entrega_turnos_verificacion_estado')->onDelete('cascade');
            $table->tinyInteger('hay_comentarios');
            $table->string('comentarios')->nullable();
            $table->bigInteger('valor')->nullable();
            $table->integer('carga_inicial', 3)->nullable()->change();
            $table->integer('carga_final', 3)->nullable()->change();
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
        Schema::dropIfExists('entrega_turnos_verificacion_bitacora');
    }
}
