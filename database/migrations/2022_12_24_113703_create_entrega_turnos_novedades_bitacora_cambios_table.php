<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEntregaTurnosNovedadesBitacoraCambiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_novedades_bitacora_cambios', function (Blueprint $table) {
            $table->id('id_cambio');
            $table->foreignId('id_novedad')->references('id_novedad')->on('entrega_turnos_novedades_bitacora')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('id_autor_cambio')->nullable();
            $table->tinyInteger('revision_antes')->comment('0 si no está revisado, 1 si está revisando y 2 si está revisado');
            $table->tinyInteger('revision_despues')->comment('0 si no está revisado, 1 si está revisando y 2 si está revisado');
            $table->text('comentarios')->nullable();
            $table->text('imagen_adjunta')->nullable();
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
        Schema::dropIfExists('entrega_turnos_novedades_bitacora_cambios');
    }
}
