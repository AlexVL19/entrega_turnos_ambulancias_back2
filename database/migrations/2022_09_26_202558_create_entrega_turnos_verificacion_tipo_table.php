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
            $table->unsignedBigInteger('id_categoria_verificacion');
            $table->foreign('id_categoria_verificacion', 'cat_verif_foreign')->references('id_categoria_verificacion')->on('entrega_turnos_categoria_verificacion')->onDelete('cascade')->onUpdate('cascade');
            $table->tinyInteger('estado')->default(1);
            $table->tinyInteger('tipo_movil')->nullable();
            $table->tinyInteger('tiene_carga')->default(0);
            $table->tinyInteger('requiere_valores')->default(0);
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
