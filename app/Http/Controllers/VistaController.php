<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VistaController extends Controller {
    
    public function getTurnData(Request $request) {
        
        /* Si el cargo que recibe como petición es auxiliar */
        if ($request->cargo == "Auxiliar") {

            /* Crea un array de datos del turno y del vehículo, el cual se guardarán todos los datos del mismo
            en caso de que haya más turnos. */
            $lista_datos_vehiculo = [];
            $lista_datos_turno = [];

            /* Crea una query la cual obtiene los datos de la apertura en los cuales el auxiliar o el 
            conductor está y que todavía estén activos. */
            $query_aperturas_auxiliar = "SELECT IdTurno, fecha_apertura, hora_apertura, REQVEH, Turno FROM 
            servicio_turnos_aperturas WHERE estado = 1 AND REQENF = ?";

            $result_aperturas_auxiliar = DB::connection()->select(DB::raw($query_aperturas_auxiliar), [$request->id_cargo]);

            /* Itera sobre cada apertura que exista y consigue los datos del vehículo de la apertura */
            for ($index = 0; $index < count($result_aperturas_auxiliar); $index++) { 
                $query_datos_vehiculo = "SELECT VEHNOM, placa FROM equipos WHERE VEHCOD = ?";

                $result_datos_vehiculo = DB::connection()->select(DB::raw($query_datos_vehiculo),
                [$result_aperturas_auxiliar[$index]->REQVEH]);

                array_push($lista_datos_vehiculo, $result_datos_vehiculo);
            }

            /* Itera sobre cada apertura existente y consigue el nombre del turno */
            for ($index = 0; $index < count($result_aperturas_auxiliar); $index++) { 
                $query_datos_turno = "SELECT Turno FROM turnos WHERE id_Turno = ?";

                $result_datos_turno = DB::connection()->select(DB::raw($query_datos_turno),
                [$result_aperturas_auxiliar[$index]->Turno]);

                array_push($lista_datos_turno, $result_datos_turno);
            }

            /* Agrupa todas esas respuestas en un JSON */
            return response(json_encode([
                "datos_turno" => $result_aperturas_auxiliar,
                "datos_vehiculo" => $lista_datos_vehiculo,
                "tipo_turno" => $lista_datos_turno
            ]));
        }

        /* Si es un conductor... */
        else if ($request->cargo == "Conductor") {

            /* Arrays que almacenan los datos del vehículo y el turno */
            $lista_datos_vehiculo = [];
            $lista_datos_turno = [];

            /* Hace una query la cual obtiene los datos de todas las aperturas en los cuales el conductor
            está participando y que estén activos */
            $query_aperturas_conductor = "SELECT IdTurno, fecha_apertura, hora_apertura, REQVEH, Turno FROM 
            servicio_turnos_aperturas WHERE estado = 1 AND REQENF = ?";

            $result_aperturas_conductor = DB::connection()->select(DB::raw($query_aperturas_conductor), [$request->id_cargo]);

            /* Por cada apertura que exista, consigue los datos de la móvil */
            for ($index = 0; $index < count($result_aperturas_conductor); $index++) { 
                $query_datos_vehiculo = "SELECT VEHNOM, placa FROM equipos WHERE VEHCOD = ?";

                $result_datos_vehiculo = DB::connection()->select(DB::raw($query_datos_vehiculo),
                [$result_aperturas_conductor[$index]->REQVEH]);

                array_push($lista_datos_vehiculo, $result_datos_vehiculo);
            }

            /* Por cada apertura que exista, consigue el nombre del turno */
            for ($index = 0; $index < count($result_aperturas_conductor); $index++) { 
                $query_datos_turno = "SELECT Turno FROM turnos WHERE id_Turno = ?";

                $result_datos_turno = DB::connection()->select(DB::raw($query_datos_turno),
                [$result_aperturas_conductor[$index]->Turno]);

                array_push($lista_datos_turno, $result_datos_turno);
            }

            /* Agrupa todas esas respuestas en un JSON */
            return response(json_encode([
                "datos_turno" => $result_aperturas_conductor,
                "datos_vehiculo" => $lista_datos_vehiculo,
                "tipo_turno" => $lista_datos_turno
            ]));
        }

        else {

            /* Si no es ningún conductor o un auxiliar, se devuelve un mensaje de error */
            return response(json_encode([
                "mensaje_no_encontrado" => 'No se ha podido encontrar un auxiliar o un conductor. Lo sentimos.'
            ]));
        }
    }
}
