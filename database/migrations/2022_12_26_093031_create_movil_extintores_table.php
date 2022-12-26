<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovilExtintoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movil_extintores', function (Blueprint $table) {
            $table->id('id_extintor');
            $table->unsignedInteger('id_equipo')->length(10);
            $table->foreign('id_equipo')->references('ID_Equipo')->on('equipos')->onDelete('cascade')->onUpdate('cascade');
            $table->date('fecha_expedicion');
            $table->date('fecha_vencimiento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movil_extintores');
    }
}
