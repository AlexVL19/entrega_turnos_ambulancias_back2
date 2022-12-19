<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NovedadesController extends Controller
{
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
            estado_revision = ?, fecha_revisando = ?, nota_revision = ?";
        }

        else if ($request->estado_nuevo == 2) {
            $query_actualizacion_bitacora = "UPDATE entrega_turnos_novedades_bitacora SET 
            estado_revision = ?, fecha_revision = ?, nota_revision = ?";
        }
    }
}
