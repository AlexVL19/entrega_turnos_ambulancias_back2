<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\TurnosExport;
use Maatwebsite\Excel\Facades\Excel;

class ListaTurnosController extends Controller
{
    /* Consigue todos los turnos entregados que hayan hasta el momento
    TODO: Conseguir todos los turnos que se hayan llenado solamente el día de hoy 
    */
    public function getTurnosEntregados() {
        $query_turnos_entregados = "SELECT * FROM entrega_turnos_bitacora";

        $result_turnos_entregados = DB::connection()->select(DB::raw($query_turnos_entregados));

        return $result_turnos_entregados;
    }

    /* Consigue a todos aquellos auxiliares que no estén inactivos. No requiere ningún parámetro */
    public function getAuxiliares() {
        $query_auxiliares = "SELECT Cod_Aux, Auxiliar FROM auxiliares WHERE Estado = 1";

        $result_auxiliares = DB::connection()->select(DB::raw($query_auxiliares));

        return $result_auxiliares;
    }

    /* Consigue a todos los conductores que no hayan sido desactivados, no requiere ningún parámetro */
    public function getConductores() {
        $query_conductores = "SELECT Cod_Con, Conductor FROM conductores WHERE Estado = 1";

        $result_conductores = DB::connection()->select(DB::raw($query_conductores));

        return $result_conductores;
    }

    /* Consigue el ID y el nombre de todas las móviles. No requiere ningún parámetro. */
    public function getMoviles() {
        $query_moviles = "SELECT ID_Equipo, VEHNOM FROM equipos";

        $result_moviles = DB::connection()->select(DB::raw($query_moviles));

        return $result_moviles;
    }

    /* Consigue la verificación, su respuesta y los comentarios desde la bitácora en donde el ID de
    bitácora sea igual al que se envía y los comentarios no sean nulos, ya que de lo contrario eso
    significaría que no hay novedades en esa verificación. */
    public function getNovedades(Request $request) {
        $query_novedades = "SELECT id_verificacion_tipo, id_estado_verificacion, comentarios
        FROM entrega_turnos_verificacion_bitacora WHERE id_bitacora = ? AND comentarios IS NOT NULL";

        $result_novedades = DB::connection()->select(DB::raw($query_novedades), [
            $request->id_bitacora
        ]);

        return $result_novedades;
    }

    /* Consigue las respuestas para poder imprimirlas en el front, no sin antes pasar por un procedimiento
    en el frontend. */
    public function getResponsesForNovedades() {
        $query_responses = "SELECT id_verificacion, estado_verificacion 
        FROM entrega_turnos_verificacion_estado WHERE estado = 1";

        $result_responses = DB::connection()->select(DB::raw($query_responses));

        return $result_responses;
    }

    /* Consigue todo el formulario (Verificación, respuesta, comentarios si los hay, 
    valores si los hay, o cargas también) con base al ID de bitácora. */
    public function getFormulario(Request $request) {
        $query_formulario = "SELECT id_verificacion_tipo, id_estado_verificacion, hay_comentarios, 
        comentarios, valor, carga_inicial FROM entrega_turnos_verificacion_bitacora
        WHERE id_bitacora = ?";

        $result_formulario = DB::connection()->select(DB::raw($query_formulario), [
            $request->id_bitacora
        ]);

        return $result_formulario;
    }

    /* Consulta el nivel de carga de los dispositivos pertenecientes a ese turno entregado, y asegura que
    tanto la carga inicial como la final no estén nulos. */
    public function consultarCargas(Request $request) {
        $query_cargas = "SELECT id_verificacion_tipo, carga_inicial, carga_final 
        FROM entrega_turnos_verificacion_bitacora WHERE id_bitacora = ? AND carga_inicial IS NOT NULL
        AND carga_final IS NOT NULL";

        $result_cargas = DB::connection()->select(DB::raw($query_cargas), [
            $request->id_bitacora
        ]);

        return $result_cargas;
    }

    public function consultarAseo(Request $request) {
        $query_aseo = "SELECT id_tipo_producto, id_producto_aseo, utilizado 
        FROM entrega_turnos_aseo_bitacora WHERE id_bitacora = ?";

        $result_aseo = DB::connection()->select(DB::raw($query_aseo), [
            $request->id_bitacora
        ]);

        return $result_aseo;
    }

    public function consultarTemperaturas(Request $request) {
        $query_temperaturas = "SELECT temperatura_max, temperatura_min, humedad_max, humedad_min, jornada,
        id_movil, fecha_registro FROM entrega_turnos_control_temperatura WHERE id_bitacora = ?";

        $result_temperaturas = DB::connection()->select(DB::raw($query_temperaturas), [
            $request->id_bitacora
        ]);

        return $result_temperaturas;
    }

    public function filtroRegistros(Request $request) {
        $query_base = "SELECT * FROM entrega_turnos_bitacora WHERE";

        if ($request->fecha_inicial || $request->fecha_final) {
            if ($request->fecha_inicial && !$request->fecha_final) {
                $query_base .= " fecha_registro >= " . "'" . $request->fecha_inicial . "'";
            }

            elseif ($request->fecha_final && !$request->fecha_inicial) {
                $query_base .= " fecha_registro <= " . "'" . $request->fecha_final . "'";
            }

            elseif ($request->fecha_inicial && $request->fecha_final) {
                $query_base .= " fecha_registro BETWEEN " . "'" . $request->fecha_inicial . "'" . " AND " 
                . "'" . $request->fecha_final . "'";
            }
        }

        if (isset($request->auxiliar)) {
            if ($request->fecha_inicial || $request->fecha_final || $request->conductor || 
            $request->id_movil || $request->novedades) {
                $query_base .= " AND id_auxiliar = " . $request->auxiliar;
            }
            else {
                $query_base .= " id_auxiliar = " . $request->auxiliar;
            }
        }

        if (isset($request->conductor)) {
            if ($request->fecha_inicial || $request->fecha_final || $request->auxiliar || 
            $request->id_movil || $request->novedades) {
                $query_base .= " AND id_conductor = " . $request->conductor;
            }

            else {
                $query_base .= " id_conductor = " . $request->conductor;
            }
        }

        if (isset($request->id_movil)) {
            if ($request->fecha_inicial || $request->fecha_final || $request->auxiliar || 
            $request->conductor || $request->novedades) {
                $query_base .= " AND id_movil = " . $request->id_movil;
            }

            else {
                $query_base .= " id_movil = " . $request->id_movil;
            }
        }

        if (isset($request->novedades)) {
            if ($request->fecha_inicial || $request->fecha_final || $request->auxiliar || 
            $request->conductor || $request->id_movil) {
                $query_base .= " AND novedades_formulario = " . $request->novedades;
            }

            else {
                $query_base .= " novedades_formulario = " . $request->novedades;
            }
        }

        return DB::connection()->select(DB::raw($query_base));
    }

    public function exportarDatos(Request $request) {

        $array_datos = $request->except(['id_bitacora', 'foto_automotor', 'aseo_terminal', 'formulario_cargas_llenado', 'formulario_temperatura_llenado']);
        
        $datos_tabla = new TurnosExport($array_datos);

        return Excel::download($datos_tabla, 'datos_tabla.xlsx');
    }
}
