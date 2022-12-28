<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEntregaTurnosBitacoraCambiosAuditoriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_bitacora_cambios_auditoria', function (Blueprint $table) {
            $table->id('id_cambio_auditoria');
            $table->foreignId('id_novedad')->references('id_novedad')->on('entrega_turnos_novedades_bitacora')->onUpdate('cascade')->onDelete('cascade');
            $table->tinyInteger('estado_auditoria_nuevo')->default(0)->comment('0 si no está auditado, 1 si está auditado y aprobado, 2 si está auditado pero no está aprobado');
            $table->text('comentarios_auditoria')->nullable();
            $table->integer('id_autor_cambio');
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
        Schema::dropIfExists('entrega_turnos_bitacora_cambios_auditoria');
    }
}
