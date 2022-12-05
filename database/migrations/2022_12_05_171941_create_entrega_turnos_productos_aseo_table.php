<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntregaTurnosProductosAseoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrega_turnos_productos_aseo', function (Blueprint $table) {
            $table->id('id_producto_aseo');
            $table->string('producto');
            $table->foreignId('tipo_producto')->references('id_tipo_producto')->on('entrega_turnos_tipos_productos_aseo')->onDelete('cascade');
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
        Schema::dropIfExists('entrega_turnos_productos_aseo');
    }
}
