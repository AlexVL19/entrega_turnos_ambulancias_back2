<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListaTurnosController extends Controller
{
    public function getTurnosEntregados() {
        $query_turnos_entregados = "SELECT id_bitacora, id_turno, id_movil, placa, id_auxiliar, id_conductor, 
        comentarios_auxiliar, comentarios_conductor, novedades_formulario, comentarios_entregado, fecha_registro 
        FROM entrega_turnos_bitacora";

        $result_turnos_entregados = DB::connection()->select(DB::raw($query_turnos_entregados));

        return $result_turnos_entregados;
    }
}
