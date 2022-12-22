<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\NovedadesExport;
use Maatwebsite\Excel\Facades\Excel;

class NovedadesController extends Controller {

    public function getNovedades() {
        $query_novedades = "SELECT * FROM entrega_turnos_novedades_bitacora WHERE NOT estado_revision = 2";

        $result_novedades = DB::connection()->select(DB::raw($query_novedades));

        return $result_novedades;
    }

    public function getVerificacionesNovedades() {
        $query_verificaciones = "SELECT id_verificacion_tipo, tipo_verificacion, id_categoria_verificacion
        FROM entrega_turnos_verificacion_tipo WHERE estado = 1";

        $result_verificaciones = DB::connection()->select(DB::raw($query_verificaciones));

        return $result_verificaciones;
    }

    public function findTurno(Request $request) {
        $query_htrabajadas = "SELECT Turno FROM htrabajadas WHERE Id_Hora = ? LIMIT 1";

        $result_htrabajadas = DB::connection()->select(DB::raw($query_htrabajadas), [
            $request->data
        ]);

        foreach ($result_htrabajadas as $turno) {
            $query_turnos = "SELECT Turno FROM turnos WHERE id_Turno = ?";

            $result_turnos = DB::connection()->select(DB::raw($query_turnos), [
                $turno->Turno
            ]);
        }

        return $result_turnos;
    }

    public function enviarDatosCambio(Request $request) {

        $query_actualizacion_bitacora = "";

        if ($request->estado_nuevo == 1) {
            $query_actualizacion_bitacora = "UPDATE entrega_turnos_novedades_bitacora SET 
            estado_revision = ?, fecha_revisando = ?, nota_revision = ?, imagen_adjunta = ? WHERE id_novedad = ?";

            $result_actualizacion_bitacora = DB::connection()->select(DB::raw($query_actualizacion_bitacora), [
                $request->estado_nuevo,
                NOW(),
                $request->nota_revision,
                $request->archivo_adjunto,
                $request->id_novedad
            ]);

            $query_agregar_cambio = "INSERT INTO entrega_turnos_novedades_bitacora_cambios 
            (id_novedad, revision_antes, revision_despues, comentarios, imagen_adjunta) VALUES 
            (?, ?, ?, ?, ?)";

            $result_agregar_cambio = DB::connection()->select(DB::raw($query_agregar_cambio), [
                $request->id_novedad,
                $request->estado_antiguo,
                $request->estado_nuevo,
                $request->nota_revision,
                $request->archivo_adjunto
            ]);
        } else if ($request->estado_nuevo == 2) {
            $query_actualizacion_bitacora = "UPDATE entrega_turnos_novedades_bitacora SET 
            estado_revision = ?, fecha_revision = ?, nota_revision = ?, imagen_adjunta = ? WHERE id_novedad = ?";

            $result_actualizacion_bitacora = DB::connection()->select(DB::raw($query_actualizacion_bitacora), [
                $request->estado_nuevo,
                NOW(),
                $request->nota_revision,
                $request->archivo_adjunto,
                $request->id_novedad
            ]);

            $query_agregar_cambio = "INSERT INTO entrega_turnos_novedades_bitacora_cambios 
            (id_novedad, revision_antes, revision_despues, comentarios, imagen_adjunta) VALUES 
            (?, ?, ?, ?, ?)";

            $result_agregar_cambio = DB::connection()->select(DB::raw($query_agregar_cambio), [
                $request->id_novedad,
                $request->estado_antiguo,
                $request->estado_nuevo,
                $request->nota_revision,
                $request->archivo_adjunto
            ]);
        }
    }

    public function getCategoriasNovedad() {
        $query_categorias = "SELECT * FROM entrega_turnos_categoria_verificacion";

        $result_categorias = DB::connection()->select(DB::raw($query_categorias));

        return $result_categorias;
    }

    public function getMovilesNovedad() {
        $query_moviles = "SELECT VEHNOM AS nombre, ID_Equipo AS id_equipo FROM equipos";

        $result_moviles = DB::connection()->select(DB::raw($query_moviles));

        return $result_moviles;
    }

    public function filtro(Request $request) {
        $query_base_buscar_novedades = "SELECT * FROM entrega_turnos_novedades_bitacora WHERE ";
        $query_base_categorias = "";

        if (isset($request->fecha_inicial) && isset($request->fecha_final)) {
            $query_base_buscar_novedades .= "fecha_creacion BETWEEN "
             . $request->fecha_inicial . " AND " . $request->fecha_final;
        }

        if (isset($request->movil)) {
            $query_base_buscar_novedades .= " AND id_movil = " . $request->movil;
        }

        if (isset($request->categoria)) {
            $query_base_categorias .= "SELECT * FROM entrega_turnos_categoria_verificacion 
            WHERE id_categoria_verificacion = " . $request->categoria;
        }

        if ($query_base_buscar_novedades !== "") {
            $result_buscar_novedades = DB::connection()->select(DB::raw($query_base_buscar_novedades));
        }

        if ($query_base_categorias !== "") {
            $result_buscar_categorias = DB::connection()->select(DB::raw($query_base_categorias));
        }

        if ($result_buscar_novedades && !$result_buscar_categorias) {
            return response(json_encode([
                "novedades" => $result_buscar_novedades
            ]));
        }

        else if ($result_buscar_novedades && $result_buscar_categorias) {
            return response(json_encode([
                "novedades" => $result_buscar_novedades,
                "categorias" => $result_buscar_categorias
            ]));
        }
    }

    public function exportarDatos(Request $request) {

        $fecha_archivo = date('Y-m-d_H:i:s');

        $array_datos = [];

        foreach ($request->all() as $novedad) {
            array_push($array_datos, [
                $novedad["id_movil"],
                $novedad["id_turno"],
                $novedad["fecha_creacion"],
                $novedad["auxiliar"],
                $novedad["conductor"],
                $novedad["verificacion"],
                $novedad["categoria"],
                $novedad["comentario_nov"],
                $novedad["estado_revision"],
                $novedad["fecha_ultima_revision"],
                $novedad["nota_revision"]
            ]);
        }

        $datos_tabla = new NovedadesExport($array_datos);

        return Excel::download($datos_tabla, 'reporte_novedades_' . $fecha_archivo . '.xlsx');
    }
}
