<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\TurnosExport;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

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

        $query_novedades = "SELECT id_novedad, id_verificacion_tipo, comentarios_novedad AS comentarios, 
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
        $query_formulario = "SELECT id_bitacora, id_verificacion_tipo, id_estado_verificacion, hay_comentarios, 
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

    public function verAnexosReporte(Request $request) {

        $array_imagenes = [];

        if ($request->danos_automotor !== 0) {
            $query_anexos_reporte = "SELECT ruta_foto FROM entrega_turnos_formulario_fotos WHERE id_bitacora = ? AND ruta_foto IS NOT NULL";

            $result_anexos_reporte = DB::connection()->select(DB::raw($query_anexos_reporte), [
                $request->id_bitacora
            ]);

            foreach ($result_anexos_reporte as $anexo) {
                $ruta_foto = $anexo->ruta_foto;

                if (Storage::disk('local')->exists($ruta_foto)) {
                    $archivo = Storage::get($ruta_foto);

                    array_push($array_imagenes, [
                        "archivo" => base64_encode($archivo),
                        "mime" => Storage::mimeType($ruta_foto)
                    ]);
                }
            }

            return $array_imagenes;
        }
    }

    public function verFormularioPDF (Request $request) {
        $config_codigo = "";
        $config_version = "";
        $config_estandar = "";
        $formulario_vistas = [];
        $id_bitacora = "";
        $auxiliar = "";
        $conductor = "";
        $nom_movil = "";
        $fecha_apertura = "";
        $tipo_turno = "";

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

        $id_bitacora = $formulario_vistas[0][0]["id_bitacora"];

        $query_get_bitacora = "SELECT id_turno, id_movil, id_auxiliar, id_conductor, placa FROM entrega_turnos_bitacora WHERE id_bitacora = ?";

        $result_get_bitacora = DB::connection()->select(DB::raw($query_get_bitacora), [
            $id_bitacora
        ]);

        $id_turno = $result_get_bitacora[0]->id_turno;

        $placa_movil = $result_get_bitacora[0]->placa;

        if ($result_get_bitacora[0]->id_movil !== null) {
            $query_datos_movil = "SELECT VEHNOM FROM equipos WHERE ID_Equipo = ?";

            $result_datos_movil = DB::connection()->select(DB::raw($query_datos_movil), [
                $result_get_bitacora[0]->id_movil
            ]);

            $nom_movil = $result_datos_movil[0]->VEHNOM;
        }

        if ($result_get_bitacora[0]->id_auxiliar !== null) {
            $query_auxiliar = "SELECT documento, Auxiliar FROM auxiliares WHERE Cod_Aux = ? AND Estado = 1";

            $result_auxiliar = DB::connection()->select(DB::raw($query_auxiliar), [
                $result_get_bitacora[0]->id_auxiliar
            ]);
            
            if (count($result_auxiliar) !== 0) {
                $auxiliar = $result_auxiliar[0]->Auxiliar . ', CC ' . $result_auxiliar[0]->documento;
            }
        }

        if ($result_get_bitacora[0]->id_conductor !== null) {
            $query_conductor = "SELECT Conductor, documento FROM conductores WHERE Cod_Con = ? AND Estado = 1";

            $result_conductor = DB::connection()->select(DB::raw($query_conductor), [
                $result_get_bitacora[0]->id_conductor
            ]);

            if (count($result_conductor) !== 0) {
                $conductor = $result_conductor[0]->Conductor . ', CC ' . $result_conductor[0]->documento;
            }
        }

        $query_horas_trabajadas = "SELECT Fecha, Turno FROM htrabajadas WHERE Id_Hora = ?";

        $result_horas_trabajadas = DB::connection()->select(DB::raw($query_horas_trabajadas), [
            $result_get_bitacora[0]->id_turno
        ]);

        $fecha_apertura = $result_horas_trabajadas[0]->Fecha;

        $query_get_turno = "SELECT Turno FROM turnos WHERE id_Turno = ?";

        $result_get_turno = DB::connection()->select(DB::raw($query_get_turno), [
            $result_horas_trabajadas[0]->Turno
        ]);

        $tipo_turno = $result_get_turno[0]->Turno;


        $fecha_formato = date('Y-m-d');

        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('formato_formulario', [
            'configs_json' => $configs_json,
            'formulario_vistas' => json_encode($formulario_vistas),
            'archivo_base64' => $archivo_base64,
            'fecha_formato' => $fecha_formato,
            'id_bitacora' => $id_bitacora,
            'id_turno' => $id_turno,
            'placa_movil' => $placa_movil,
            'auxiliar' => $auxiliar,
            'conductor' => $conductor,
            'nom_movil' => $nom_movil,
            'fecha_apertura' => $fecha_apertura,
            'tipo_turno' => $tipo_turno
        ]);

        return $pdf->download('prueba.pdf');
    }

    public function verAnexo(Request $request) {
        $query_ver_anexo = "SELECT imagen_adjunta FROM entrega_turnos_novedades_bitacora_cambios WHERE id_cambio = ? LIMIT 1";

        $result_ver_anexo = DB::connection()->select(DB::raw($query_ver_anexo), [
            $request->id_cambio
        ]); 

        $ruta_archivo = $result_ver_anexo[0]->imagen_adjunta;

        if (Storage::disk('local')->exists($ruta_archivo)) {
            return Storage::download($ruta_archivo);
        }
    }

    public function getExtensionAnexo(Request $request) {
        $query_get_extension = "SELECT imagen_adjunta FROM entrega_turnos_novedades_bitacora_cambios WHERE id_cambio = ? LIMIT 1";

        $result_get_extension = DB::connection()->select(DB::raw($query_get_extension), [
            $request->id_cambio
        ]); 

        $ruta_archivo = $result_get_extension[0]->imagen_adjunta;

        if (Storage::disk('local')->exists($ruta_archivo)) {
            $extension_anexo = pathinfo(storage_path($ruta_archivo), PATHINFO_EXTENSION);

            return $extension_anexo;
        }
    }

    public function verCambiosNovedad(Request $request) {
        $query_ver_cambios = "SELECT id_cambio, revision_despues, comentarios, fecha_registro, imagen_adjunta 
        FROM entrega_turnos_novedades_bitacora_cambios WHERE id_novedad = ? ORDER BY id_cambio DESC";

        $result_ver_cambios = DB::connection()->select(DB::raw($query_ver_cambios), [
            $request->id_novedad
        ]);

        return $result_ver_cambios;
    }
}
