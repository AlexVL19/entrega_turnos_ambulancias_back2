<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEntregaTurnosNovedadesBitacoraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_novedades_bitacora', function (Blueprint $table) {
            $table->id('id_novedad');
            $table->foreignId('id_bitacora')->references('id_bitacora')->on('entrega_turnos_bitacora')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('id_turno')->length(20);
            $table->foreign('id_turno')->references('Id_Hora')->on('htrabajadas')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('id_movil')->length(10);
            $table->foreign('id_movil')->references('ID_Equipo')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedTinyInteger('id_auxiliar')->nullable()->length(3);
            $table->foreign('id_auxiliar')->nullable()->references('Cod_Aux')->on('auxiliares')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedTinyInteger('id_conductor')->nullable()->length(3);
            $table->foreign('id_conductor')->nullable()->references('Cod_Con')->on('conductores')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('id_verificacion_tipo');
            $table->foreign('id_verificacion_tipo', 'verif_tipo_foreign3')->references('id_verificacion_tipo')->on('entrega_turnos_verificacion_tipo')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('id_categoria_verificacion');
            $table->foreign('id_categoria_verificacion', 'cat_verif_foreign3')->references('id_categoria_verificacion')->on('entrega_turnos_categoria_verificacion')->onDelete('cascade')->onUpdate('cascade');
            $table->text('comentarios_novedad')->nullable();
            $table->tinyInteger('estado_revision')->default(0)->comment('0 si no ha sido revisado, 1 si está en revisión y 2 si ya ha sido revisado');
            $table->tinyInteger('estado_auditoria')->default(0)->comment('0 si no está auditado, 1 si está auditado y aprobado, 2 si está auditado pero no está aprobado');
            $table->dateTime('fecha_creacion')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('fecha_revisando')->nullable();
            $table->dateTime('fecha_revision')->nullable();
            $table->dateTime('fecha_auditoria')->nullable();
            $table->text('nota_revision')->nullable();
            $table->text('nota_auditoria')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entrega_turnos_novedades_bitacora');
    }
}
