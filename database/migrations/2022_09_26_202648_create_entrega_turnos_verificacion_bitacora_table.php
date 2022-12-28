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
            $table->unsignedBigInteger('id_verificacion_tipo');
            $table->foreign('id_verificacion_tipo', 'verif_tipo_foreign')->references('id_verificacion_tipo')->on('entrega_turnos_verificacion_tipo')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('id_categoria_verificacion');
            $table->foreign('id_categoria_verificacion', 'cat_verif_foreign2')->references('id_categoria_verificacion')->on('entrega_turnos_categoria_verificacion')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('id_estado_verificacion');
            $table->foreign('id_estado_verificacion', 'est_verif_foreign')->references('id_verificacion')->on('entrega_turnos_verificacion_estado')->onDelete('cascade')->onUpdate('cascade');
            $table->tinyInteger('hay_comentarios');
            $table->string('comentarios')->nullable();
            $table->bigInteger('valor')->nullable();
            $table->tinyinteger('carga_inicial')->nullable()->length(3);
            $table->tinyInteger('carga_final')->nullable()->length(3);
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
