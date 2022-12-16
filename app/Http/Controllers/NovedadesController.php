<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NovedadesController extends Controller
{
    public function getNovedades() {
        $query_novedades = "SELECT * FROM entrega_turnos_novedades_bitacora";
    }
}
