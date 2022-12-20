<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}
