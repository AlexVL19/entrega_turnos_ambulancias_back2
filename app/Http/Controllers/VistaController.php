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
            $formulario_llenado = [];
            $datos_comentarios = [];
            $banderas_formularios = [];

            /* Crea una query la cual obtiene los datos de la apertura en los cuales el auxiliar o el 
            conductor está y que todavía estén activos. */
            $query_aperturas_auxiliar = "SELECT Id_Hora as IdTurno, Fecha as fecha_apertura, 
            comentarios_operador, ID_Equipo as REQVEH, Turno FROM 
            htrabajadas WHERE Activo = 2 AND ID_Auxiliar = ? AND ID_Equipo IS NOT NULL AND Turno IS NOT NULL";

            $result_aperturas_auxiliar = DB::connection()->select(DB::raw($query_aperturas_auxiliar), [$request->id_cargo]);

            /* Itera sobre cada apertura que exista */
            for ($index = 0; $index < count($result_aperturas_auxiliar); $index++) { 

                if (isset($result_aperturas_auxiliar[$index]->REQVEH)) {
                    /* Consigue varios datos del vehículo como el nombre y su placa */
                    $query_datos_vehiculo = "SELECT VEHNOM, placa FROM equipos WHERE VEHCOD = ?";

                    $result_datos_vehiculo = DB::connection()->select(DB::raw($query_datos_vehiculo),
                    [$result_aperturas_auxiliar[$index]->REQVEH]);

                    /* Por cada coincidencia va insertando en este array */
                    array_push($lista_datos_vehiculo, $result_datos_vehiculo);
                }

                /* Consigue el nombre del turno cuyo ID coincida con el que se da como parámetro */
                $query_datos_turno = "SELECT Turno, tiene_aseo, jornada_temperatura FROM turnos WHERE id_Turno = ?";

                $result_datos_turno = DB::connection()->select(DB::raw($query_datos_turno),
                [$result_aperturas_auxiliar[$index]->Turno]);

                if (isset($result_datos_turno[$index]->jornada_temperatura)) {
                    $result_datos_turno[$index]->jornada_temperatura = '1';
                }

                if (isset($result_datos_turno[$index]->Turno)) {
                    $result_datos_turno[$index]->Turno = 'N/A';
                }

                /* Por cada coincidencia va agregando a este array */
                array_push($lista_datos_turno, $result_datos_turno);

                /* Evalúa si esa apertura contiene ya un registro en bitácora, lo que indica que
                   ya llenó el formulario */
                $query_form_llenado = "SELECT formulario_llenado, id_bitacora FROM entrega_turnos_bitacora
                WHERE id_turno = ?";

                $result_form_llenado = DB::connection()->select(DB::raw($query_form_llenado), [
                    $result_aperturas_auxiliar[$index]->IdTurno
                ]);

                /* Condicional que se ejecuta en caso de que no traiga ningún registro.  */
                if (count($result_form_llenado) == 0) {
                    array_push($formulario_llenado, json_encode([
                        'formulario_llenado' => null
                    ]));
                }

                /* Por cada coincidencia va agregando en este array */
                else {
                    array_push($formulario_llenado, $result_form_llenado);
                }

                //Query que trae los comentarios del turno anterior
                $query_comentarios_anterior = "SELECT comentarios_entregado FROM entrega_turnos_bitacora WHERE
                id_turno = ?";

                $result_comentarios_anterior = DB::connection()->select(DB::raw($query_comentarios_anterior), [
                    ($result_aperturas_auxiliar[$index]->IdTurno - 1)
                ]);

                if (count($result_comentarios_anterior) == 0) {
                    $result_comentarios_anterior = json_encode([
                        "comentarios_entregado" => null
                    ]);
                }

                array_push($datos_comentarios, $result_comentarios_anterior);

                // Query que trae las banderas del turno anteriormente creado, e indica si el respectivo
                // formulario fue llenado o no.
                $query_banderas_formularios = "SELECT aseo_terminal, formulario_cargas_llenado, 
                formulario_temperatura_llenado FROM entrega_turnos_bitacora WHERE id_turno = ?";

                $result_banderas_formularios = DB::connection()->select(DB::raw($query_banderas_formularios), [
                    $result_aperturas_auxiliar[$index]->IdTurno
                ]);

                // Si no existe ninguna de estas banderas, se establecen a nulo
                if (count($result_banderas_formularios) == 0) {
                    $result_banderas_formularios = json_encode([
                        "aseo_terminal" => null,
                        "formulario_cargas_llenado" => null,
                        "formulario_temperatura_llenado" => null
                    ]);
                }

                array_push($banderas_formularios, $result_banderas_formularios);
            }

            /* Agrupa todas esas respuestas en un JSON */
            return response(json_encode([
                "datos_turno" => $result_aperturas_auxiliar,
                "datos_vehiculo" => isset($lista_datos_vehiculo)? $lista_datos_vehiculo : null,
                "tipo_turno" => $lista_datos_turno,
                "formulario_llenado" => $formulario_llenado,
                "banderas_llenados" => $banderas_formularios,
                "comentarios_anterior" => $datos_comentarios
            ]));
        }

        /* Si es un conductor... */
        else if ($request->cargo == "Conductor") {

            /* Arrays que almacenan los datos del vehículo y el turno */
            $lista_datos_vehiculo = [];
            $lista_datos_turno = [];
            $formulario_llenado = [];

            /* Hace una query la cual obtiene los datos de todas las aperturas en los cuales el conductor
            está participando y que estén activos */
            $query_aperturas_conductor = "SELECT Id_Hora as IdTurno, Fecha as fecha_apertura, 
            ID_Equipo as REQVEH, Turno FROM 
            htrabajadas WHERE Activo = 2 AND ID_Conductor = ? AND ID_Equipo IS NOT NULL AND Turno IS NOT NULL";

            $result_aperturas_conductor = DB::connection()->select(DB::raw($query_aperturas_conductor), [$request->id_cargo]);

            /* Por cada apertura que exista */
            for ($index = 0; $index < count($result_aperturas_conductor); $index++) { 

                if (isset($result_aperturas_conductor[$index]->REQVEH)) {
                    /* Consigue varios datos del vehículo como el nombre y su placa */
                    $query_datos_vehiculo = "SELECT VEHNOM, placa FROM equipos WHERE VEHCOD = ?";

                    $result_datos_vehiculo = DB::connection()->select(DB::raw($query_datos_vehiculo),
                    [$result_aperturas_conductor[$index]->REQVEH]);

                    /* Por cada coincidencia va insertando en este array */
                    array_push($lista_datos_vehiculo, $result_datos_vehiculo);
                }

                /* Obtiene el nombre del turno en función del ID otorgado */
                $query_datos_turno = "SELECT Turno, tiene_aseo, jornada_temperatura FROM turnos WHERE id_Turno = ?";

                $result_datos_turno = DB::connection()->select(DB::raw($query_datos_turno),
                [$result_aperturas_conductor[$index]->Turno]);

                if ($result_datos_turno[$index]->jornada_temperatura == null) {
                    $result_datos_turno[$index]->jornada_temperatura = '1';
                }

                /* Cada coincidencia se guarda dentro del array */
                array_push($lista_datos_turno, $result_datos_turno);

                /* Verifica si la bandera de formulario llenado está activa y si hay alguno */
                $query_form_llenado = "SELECT formulario_llenado FROM entrega_turnos_bitacora
                WHERE id_turno = ?";

                $result_form_llenado = DB::connection()->select(DB::raw($query_form_llenado), [
                    $result_aperturas_conductor[$index]->IdTurno
                ]);

                /* Si no hay ninguno, trae un null dentro de un json */
                if (count($result_form_llenado) == 0) {
                    array_push($formulario_llenado, json_encode([
                        'formulario_llenado' => null
                    ]));
                }

                else {
                    /* Cada coincidencia se guarda dentro del array */
                    array_push($formulario_llenado, $result_form_llenado);
                }

                //Query que trae los comentarios del turno anterior
                $query_comentarios_anterior = "SELECT comentarios_entregado FROM entrega_turnos_bitacora WHERE
                id_turno = ?";

                $result_comentarios_anterior = DB::connection()->select(DB::raw($query_comentarios_anterior), [
                    ($result_aperturas_conductor[$index]->IdTurno - 1)
                ]);

                if (count($result_comentarios_anterior) == 0) {
                    $result_comentarios_anterior = json_encode([
                        "comentarios_entregado" => null
                    ]);
                }

                array_push($datos_comentarios, $result_comentarios_anterior);

                // Query que trae las banderas del turno anteriormente creado, e indica si el respectivo
                // formulario fue llenado o no.
                $query_banderas_formularios = "SELECT aseo_terminal, formulario_cargas_llenado, 
                formulario_temperatura_llenado FROM entrega_turnos_bitacora WHERE id_turno = ?";

                $result_banderas_formularios = DB::connection()->select(DB::raw($query_banderas_formularios), [
                    $result_aperturas_conductor[$index]->IdTurno
                ]);

                // Si no existe ninguna de estas banderas, se establecen a nulo
                if (count($result_banderas_formularios) == 0) {
                    $result_banderas_formularios = json_encode([
                        "aseo_terminal" => null,
                        "formulario_cargas_llenado" => null,
                        "formulario_temperatura_llenado" => null
                    ]);
                }

                array_push($banderas_formularios, $result_banderas_formularios);
            }

            /* Agrupa todas esas respuestas en un JSON */
            return response(json_encode([
                "datos_turno" => $result_aperturas_conductor,
                "datos_vehiculo" => isset($lista_datos_vehiculo) ? $lista_datos_vehiculo : null,
                "tipo_turno" => $lista_datos_turno,
                "formulario_llenado" => $formulario_llenado,
                "banderas_llenados" => $banderas_formularios,
                "comentarios_anterior" => $datos_comentarios
            ]));
        }

        else {

            /* Si no es ningún conductor o un auxiliar, se devuelve un mensaje de error */
            return response(json_encode([
                "mensaje_no_encontrado" => 'No se ha podido encontrar un auxiliar o un conductor. Lo sentimos.'
            ]));
        }
    }

    public function addCommentsToBitacora(Request $request) {

        //Si encuentra algún campo para ser insertado
        if (count($request->all()) > 0) {

            //Actualiza el registro en bitácora con el ID de turno especificado para que incluya ese comentario
            $query_actualizacion = "UPDATE entrega_turnos_bitacora SET comentarios_entregado = ? WHERE id_turno = ?";

            $result_actualizacion = DB::connection()->select(DB::raw($query_actualizacion), [
                $request->comentarios_entregado, 
                $request->id_turno
            ]);
        }

        //Al dar clic en terminar turno, se desactiva el turno que hay en htrabajadas, dando así por terminado el turno
        $query_cerrar_turno = "UPDATE htrabajadas SET Activo = 1 WHERE Id_Hora = ?";

        $result_cerrar_turno = DB::connection()->select(DB::raw($query_cerrar_turno), [
            $request->id_turno
        ]);
    }
}
