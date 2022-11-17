<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListaTurnosController extends Controller
{
    public function getTurnosEntregados() {
        $query_turnos_entregados = "SELECT * FROM entrega_turnos_bitacora";

        $result_turnos_entregados = DB::connection()->select(DB::raw($query_turnos_entregados));

        return $result_turnos_entregados;
    }

    public function getAuxiliares() {
        $query_auxiliares = "SELECT Cod_Aux, Auxiliar FROM auxiliares WHERE Estado = 1";

        $result_auxiliares = DB::connection()->select(DB::raw($query_auxiliares));

        return $result_auxiliares;
    }

    public function getConductores() {
        $query_conductores = "SELECT Cod_Con, Conductor FROM conductores WHERE Estado = 1";

        $result_conductores = DB::connection()->select(DB::raw($query_conductores));

        return $result_conductores;
    }

    public function getMoviles() {
        $query_moviles = "SELECT ID_Equipo, VEHNOM FROM equipos";

        $result_moviles = DB::connection()->select(DB::raw($query_moviles));

        return $result_moviles;
    }

    public function getNovedades(Request $request) {
        $query_novedades = "SELECT id_verificacion_tipo, id_estado_verificacion, comentarios
        FROM entrega_turnos_verificacion_bitacora WHERE id_bitacora = ? AND comentarios IS NOT NULL";

        $result_novedades = DB::connection()->select(DB::raw($query_novedades), [
            $request->id_bitacora
        ]);

        return $result_novedades;
    }

    public function getResponsesForNovedades() {
        $query_responses = "SELECT id_verificacion, estado_verificacion 
        FROM entrega_turnos_verificacion_estado WHERE estado = 1";

        $result_responses = DB::connection()->select(DB::raw($query_responses));

        return $result_responses;
    }

    public function getFormulario(Request $request) {
        $query_formulario = "SELECT id_verificacion_tipo, id_estado_verificacion, hay_comentarios, 
        comentarios, valor, carga_inicial FROM entrega_turnos_verificacion_bitacora
        WHERE id_bitacora = ?";

        $result_formulario = DB::connection()->select(DB::raw($query_formulario), [
            $request->id_bitacora
        ]);

        return $result_formulario;
    }
}
