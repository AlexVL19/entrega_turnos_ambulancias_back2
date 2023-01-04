<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\TurnosExport;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $query_novedades = "SELECT id_verificacion_tipo, comentarios_novedad AS comentarios, 
        estado_revision, estado_auditoria, nota_revision, nota_auditoria, 
        fecha_revision, fecha_auditoria FROM entrega_turnos_novedades_bitacora WHERE id_bitacora = ?";

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

    /* Consulta el reporte de aseo y desinfección cuyo id de bitácora coincida con el que se ha enviado.
    Consigue todos los productos y si han sido utilizados o no. */
    public function consultarAseo(Request $request) {
        $query_aseo = "SELECT id_tipo_producto, id_producto_aseo, utilizado 
        FROM entrega_turnos_aseo_bitacora WHERE id_bitacora = ?";

        $result_aseo = DB::connection()->select(DB::raw($query_aseo), [
            $request->id_bitacora
        ]);

        return $result_aseo;
    }

    /* Consulta el reporte de temperatura y humedad cuyo id de bitácora coincida con el que se ha enviado.
    Consigue la temperatura máxima y mínima, y la humedad máxima y mínima. */
    public function consultarTemperaturas(Request $request) {
        $query_temperaturas = "SELECT temperatura_max, temperatura_min, humedad, jornada,
        id_movil, fecha_registro FROM entrega_turnos_control_temperatura WHERE id_bitacora = ?";

        $result_temperaturas = DB::connection()->select(DB::raw($query_temperaturas), [
            $request->id_bitacora
        ]);

        return $result_temperaturas;
    }

    /* Filtra los turnos entregados que hay en bitácora mediante un concatenado de queries. En caso de que
    alguno de los filtros contenga algún valor, y también si este valor viene en conjunto con otros valores,
    se concatena con la query base. Cuando haya pasado por todas estas condiciones, se ejecuta la query. */
    public function filtroRegistros(Request $request) {
        $query_base = "SELECT * FROM entrega_turnos_bitacora WHERE";

        if ($request->fecha_inicial || $request->fecha_final) {
            if ($request->fecha_inicial && !$request->fecha_final) {
                $query_base .= " fecha_registro >= " . "'" . $request->fecha_inicial . " 00:00:00" . "'";
            }

            elseif ($request->fecha_final && !$request->fecha_inicial) {
                $query_base .= " fecha_registro <= " . "'" . $request->fecha_final .  " 23:59:00" . "'";
            }

            elseif ($request->fecha_inicial && $request->fecha_final) {
                $query_base .= " fecha_registro >= " .
                "'" . $request->fecha_inicial . " 00:00:00" . "'" . " AND fecha_registro <= " . "'" . $request->fecha_final . " 23:59:59" . "'";
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

    /* Función que permite exportar los turnos que se muestran en el front (filtrados o no), a un documento
    de Excel (.xlsx), ideal para reportes. */
    public function exportarDatos(Request $request) {

        $fecha_archivo = date('Y-m-d_H:i:s'); // Consigue la fecha actual para posteriomente concatenar

        $array_datos = []; // Array en el cual se van a leer los datos cuando se exporte a Excel

        // Recorriendo todo lo que haya dentro del request, se pushea al array de datos para que sea legible
        // por el exportador
        foreach ($request->all() as $turno) {
            array_push($array_datos, [
                $turno["id_turno"],
                $turno["id_movil"],
                $turno["placa"],
                $turno["id_auxiliar"],
                $turno["id_conductor"],
                $turno["comentarios_conductor"],
                $turno["comentarios_auxiliar"],
                $turno["comentarios_entregado"],
                $turno["novedades_formulario"],
                $turno["fecha_registro"],
            ]);
        }
        
        // Se llama al constructor que permite exportar los datos a Excel y le pasamos el array de datos
        $datos_tabla = new TurnosExport($array_datos);

        /* Luego se devuelve el documento de Excel listo para descargar, en conjunto con el nombre y la
        fecha del archivo. */
        return Excel::download($datos_tabla, 'reporte_turnos_entregados_' . $fecha_archivo . '.xlsx');
    }

    public function verReporte(Request $request) {
        if ($request->danos_automotor !== 0) {
            $query_foto = "SELECT foto_automotor FROM entrega_turnos_bitacora WHERE id_bitacora = ? AND foto_automotor IS NOT NULL AND NOT danos_automotor = 0";

            $result_foto = DB::connection()->select(DB::raw($query_foto), [
                $request->id_bitacora
            ]);

            $ruta_foto = $result_foto[0]->foto_automotor;

            if (Storage::disk('local')->exists($ruta_foto)) {
                $archivo = Storage::get($ruta_foto);

                return base64_encode($archivo);
            }

            else {
                return 'No encontrado';
            }
        }

        else {
            return response(json_encode(array('mensaje' => 'No encontrado')));
        }
    }

    public function verFormularioPDF (Request $request) {
        $config_codigo = "";
        $config_version = "";
        $config_estandar = "";
        $formulario_vistas = [];

        $archivo_antes = file_get_contents(public_path('images/red-logo.png'));

        $archivo_base64 = base64_encode($archivo_antes);

        $query_configs_formato = "SELECT `value` FROM configs WHERE `key` LIKE 'entrega_turnos_formato%'";

        $result_configs_formato = DB::connection()->select(DB::raw($query_configs_formato));

        foreach ($result_configs_formato as $config) {
            if (str_contains($config->value, 'GINF')) {
                $config_codigo = $config->value;
            }

            if (str_contains($config->value, '20')) {
                $config_version = $config->value;
            }

            if (str_contains($config->value, 'Procesos')) {
                $config_estandar = $config->value;
            }
        }

        $configs_json = json_encode([
            "codigo" => $config_codigo,
            "version" => $config_version,
            "estandar" => $config_estandar,
        ]);

        foreach ($request->all() as $categoria) {
            array_push($formulario_vistas, $categoria['formularios']);
        }

        $fecha_formato = date('Y-m-d');

        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('formato_formulario', [
            'configs_json' => $configs_json,
            'formulario_vistas' => json_encode($formulario_vistas),
            'archivo_base64' => $archivo_base64,
            'fecha_formato' => $fecha_formato
        ]);

        return $pdf->download('prueba.pdf');
    }
}
