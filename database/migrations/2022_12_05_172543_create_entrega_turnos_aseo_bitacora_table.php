<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntregaTurnosAseoBitacoraTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_aseo_bitacora', function (Blueprint $table) {
            $table->id('id_bitacora_aseo');
            $table->foreignId('id_bitacora')->references('id_bitacora')->on('entrega_turnos_bitacora')->onDelete('cascade');
            $table->foreignId('id_tipo_producto')->references('id_tipo_producto')->on('entrega_turnos_tipos_productos_aseo')->onDelete('cascade');
            $table->foreignId('id_producto_aseo')->references('id_producto_aseo')->on('entrega_turnos_productos_aseo')->onDelete('cascade');
            $table->tinyInteger('utilizado');
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
        Schema::dropIfExists('entrega_turnos_aseo_bitacora');
    }
}
