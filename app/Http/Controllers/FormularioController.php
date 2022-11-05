<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormularioController extends Controller
{

    public function getVerifications () {

        //Se consigue los tipos de verificación
        $query_verificaciones = "SELECT id_verificacion_tipo, tipo_verificacion, id_categoria_verificacion, requiere_valores FROM entrega_turnos_verificacion_tipo";

        //Se ejecutan la query almacenándola en una variable que contendrá una respuesta
        $result_verificaciones = DB::connection()->select(DB::raw($query_verificaciones));

        //Se agrupan las respuestas en un JSON, agrupando las verificaciones con su respectiva clave.
        return response(json_encode([
            "verificaciones" => $result_verificaciones,
            "contador" => count($result_verificaciones)
        ]));
    }

    public function getCategories() {

        //Se consigue toda la información de las categorías gracias al siguiente string
        $query_categories = "SELECT * FROM entrega_turnos_categoria_verificacion";

        //Ejecuta la query tomando como base el string de antes
        $result_categories = DB::connection()->select(DB::raw($query_categories));

        //Devuelve una respuesta a la query, si no encuentra nada el array estará vacío
        return $result_categories;
    }

    public function getResponses() {

        //Arrays separados los cuales van a recibir aquellas respuestas pertenecientes a su categoría
        $no_corresponde = [];
        $estado_revisiones = [];
        $estado_llantas = [];
        $estado_maletines = [];
        $estado_verificacion_vehiculo = [];

        //Queries que toman las posibles respuestas conforme a su categoría de respuesta.
        $query_estados = "SELECT * FROM entrega_turnos_verificacion_estado WHERE estado = 1";

        //Resultado de las queries que se almacenan en una variable.
        $resultados_estados = DB::connection()->select(DB::raw($query_estados));

        /* Si el array no está vacío, es decir, que hay registros, empieza a filtrar
           por cada categoría de respuesta */
        if(count($resultados_estados) > 0) {
            foreach($resultados_estados as $resultado_estado) {

                /* Si la categoría de respuesta es la especificada, pushea en el respectivo array */
                if ($resultado_estado->id_categoria_respuesta == 1) {
                    array_push($no_corresponde, $resultado_estado);
                }
                
                if ($resultado_estado->id_categoria_respuesta == 2) {
                    array_push($estado_revisiones, $resultado_estado);
                }

                if ($resultado_estado->id_categoria_respuesta == 3) {
                    array_push($estado_llantas, $resultado_estado);
                }

                if ($resultado_estado->id_categoria_respuesta == 4) {
                    array_push($estado_maletines, $resultado_estado);
                }

                if ($resultado_estado->id_categoria_respuesta == 5) {
                    array_push($estado_verificacion_vehiculo, $resultado_estado);
                }
            }
        }

        //Se agrupan en un JSON con una clave que contendrá todas las posibles respuestas.
        return response(json_encode([
            "no_corresponde" => $no_corresponde,
            "estado_revisiones" => $estado_revisiones,
            "estado_llantas" => $estado_llantas,
            "estado_maletines" => $estado_maletines,
            "si_no" => $estado_verificacion_vehiculo
        ]));
    }

    public function insertIntoBitacora (Request $request) {

        /* Validaciones que comprueban si cada campo cumple con las debidas reglas descritas aquí abajo: */ 
        $fields = $request->validate([
            'id_verificacion_tipo' => 'integer', //Comprueba si los caracteres especificados en la expresión regular coinciden con el valor
            'id_estado_verificacion' => 'integer',
            'comentarios' => 'nullable|string', //Comprueba si es una cadena de texto
            'valor' => 'nullable|integer',
            'carga' => 'nullable|integer'
        ]);

        /* Se recorre cada posición del objeto Request y se hace lo siguiente: */
           for ($index = 0; $index < count($request->all()); $index++) { 

               /* Por cada posición, hace una query para insertar */
               $query_insert = "INSERT INTO entrega_turnos_verificacion_bitacora 
               (id_bitacora, id_verificacion_tipo, id_estado_verificacion, hay_comentarios, comentarios, valor, carga_inicial)
               VALUES (?, ?, ?, ?, ?, ?, ?)";

               /* Si el índice existe, la query se ejecuta tomando todos los valores existentes en
                  la posición actual */
               if (isset($request[$index])) {
                   DB::connection()->select(DB::raw($query_insert), [
                    $request[$index]["id_bitacora_turnos"], 
                    $request[$index]["id_tipo_verificacion"],
                    $request[$index]["id_estado_verificacion"],
                    $request[$index]["activarComentario"], 
                    $request[$index]["comentarios"],
                    $request[$index]["valor"],
                    $request[$index]["carga"]
                    ]);
               }

               /* Si no existe, se devuelve un mensaje de error en conjunto con el índice que está fallando */
               else {
                   echo("Error en el índice" . $index);
               }
           }
    }

    public function getAmbulanceData ($id_movil) {

        $query_soat = "SELECT fecha_vencimiento FROM movil_soat WHERE id_equipo = ? 
        ORDER BY id_soat DESC LIMIT 1";

        $result_soat = DB::connection()->select(DB::raw($query_soat), [
            $id_movil
        ]);

        $query_extintores = "SELECT fecha_vencimiento FROM movil_extintores WHERE id_equipo = ? 
        ORDER BY id_extintor DESC LIMIT 1";

        $result_extintores = DB::connection()->select(DB::raw($query_extintores), [
            $id_movil
        ]);

        $query_tecnomecanica = "SELECT fecha_revision FROM movil_tecnomecanica WHERE id_equipo = ? 
        ORDER BY id_tecnomecanica DESC LIMIT 1";

        $result_tecnomecanica = DB::connection()->select(DB::raw($query_tecnomecanica), [
            $id_movil
        ]);

        $query_cambios_hidraulica = "SELECT fecha_ultimo_cambio FROM movil_cambios_aceite_hidraulico 
        WHERE id_equipo = ? ORDER BY id_cambio_aceite_hidraulico DESC LIMIT 1";

        $result_cambios_hidraulica = DB::connection()->select(DB::raw($query_cambios_hidraulica), [
            $id_movil
        ]);

        $query_cambios_aceite = "SELECT fecha_ultimo_cambio FROM movil_cambios_aceite_motor 
        WHERE id_equipo = ? ORDER BY id_cambio_aceite_motor DESC LIMIT 1";

        $result_cambios_aceite = DB::connection()->select(DB::raw($query_cambios_aceite), [
            $id_movil
        ]);

        $query_cambios_frenos = "SELECT fecha_ultimo_cambio FROM movil_cambios_frenos
        WHERE id_equipo = ? ORDER BY id_cambio_frenos DESC LIMIT 1";

        $result_cambios_frenos = DB::connection()->select(DB::raw($query_cambios_frenos), [
            $id_movil
        ]);

        $query_cambios_suspension = "SELECT fecha_ultimo_cambio FROM movil_cambios_suspension
        WHERE id_equipo = ? ORDER BY id_cambio_suspension DESC LIMIT 1"; 

        $result_cambios_suspension = DB::connection()->select(DB::raw($query_cambios_suspension), [
            $id_movil
        ]);

        /* Devuelve todas las respuestas agrupadas en un JSON*/
            return response(json_encode([
                "fecha_soat" => $result_soat,
                "fecha_extintor" => $result_extintores,
                "revision_tecno" => $result_tecnomecanica,
                "cambios_hidraulica" => $result_cambios_hidraulica,
                "cambios_aceite" => $result_cambios_aceite,
                "cambios_frenos" => $result_cambios_frenos,
                "cambios_suspension" => $result_cambios_suspension
            ]));

        

        // /* Consigue el soat, la revisión tecnomecánica y el extintor de la hoja de vida en donde el ID de la móvil
        //    coincida con el parámetro y que además esté disponible */
        // $query_hojavida = "SELECT id_soat, id_tecnomecanica, id_extintor FROM movil_hojavida WHERE ID_Equipo = ?
        // AND estado = 1 LIMIT 1";

        // $result_hojavida = DB::connection()->select(DB::raw($query_hojavida), [
        //     $id_movil
        // ]);

        // /* Si encuentra algún registro en el resultado de la query */
        // if (count($result_hojavida) > 0) {

        //     /* Recoge todas las fechas de expedición y de revisión en función de los IDs que se trajeron */
        //     $query_soat = "SELECT fecha_vencimiento FROM movil_soat WHERE id_soat = ?";

        //     $query_extintores = "SELECT fecha_vencimiento FROM movil_extintores WHERE id_extintor = ?";

        //     $query_tecnomecanica = "SELECT fecha_revision FROM movil_tecnomecanica WHERE id_tecnomecanica = ?";

        //     $result_soat = DB::connection()->select(DB::raw($query_soat), [$result_hojavida[0]->id_soat]);

        //     $result_extintores = DB::connection()->select(DB::raw($query_extintores), [$result_hojavida[0]->id_extintor]);

        //     $result_tecno = DB::connection()->select(DB::raw($query_tecnomecanica), [$result_hojavida[0]->id_tecnomecanica]);
        // }

        // /* Si no hay nada, se trae un mensaje de error */
        // else {
        //     return response(json_encode([
        //         "mensaje_no_encontrado" => "No se han podido encontrar fechas de acuerdo a esta fecha"
        //     ]));
        // }
    }

    public function insertIntoMainBitacora (Request $request) {

        $fields = $request->validate([
            'id_turno' => 'required|integer',
            'id_usuario' => 'required|integer',
            'danos_automotor' => 'required', // Valida si es requerido
            'foto_automotor' => 'nullable|string', //Valida si puede ser nulo y si es una cadena de texto
            'comentarios_conductor' => 'nullable|string',
            'comentarios_auxiliar' => 'nullable|string'
        ]);

        //Query que trae los IDs del vehículo y el ID del auxiliar y el conductor.
        $query_aperturas = "SELECT ID_Equipo as REQVEH, ID_Auxiliar as REQENF, ID_Conductor as REQCON FROM 
        htrabajadas WHERE Id_Hora = ?";

        $result_aperturas = DB::connection()->select(DB::raw($query_aperturas), [$fields["id_turno"]]);

        //Query que trae el cargo del usuario cuyo ID sea el mismo que en la request
        $query_usuario = "SELECT cargo FROM usuarios_app WHERE Id = ? LIMIT 1";

        $result_usuario = DB::connection()->select(DB::raw($query_usuario), [$fields["id_usuario"]]);

        //Si el cargo es auxiliar
        if ($result_usuario[0]->cargo == 'Auxiliar') {

            //Query que trae los datos de la móvil con base al código del vehículo especificado en las aperturas
            $query_movil = "SELECT placa FROM equipos WHERE VEHCOD = ?";

            $result_movil = DB::connection()->select(DB::raw($query_movil), [$result_aperturas[0]->REQVEH]);
            
            //Query de inserción de bitácora
            $query_insert_bitacora = "INSERT INTO entrega_turnos_bitacora (id_turno, id_movil, placa, 
            id_auxiliar, danos_automotor, foto_automotor, comentarios_auxiliar) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        
            //Verifica si el string de base64 existe o no para empezar la operación
            if ($fields["foto_automotor"] !== null || $fields["foto_automotor"] !== "") {

                // Se almacena el string en base64 de la foto de la móvil
                $imagen = $fields["foto_automotor"];
        
                //Se reemplaza el identificador al inicio del texto por un string vacío
                $imagen = str_replace('data:image/png;base64', '', $imagen);
        
                //Se reemplaza ese espacio por un caracter
                $imagen = str_replace(' ', '+', $imagen);

                $nombre_carpeta = $result_aperturas[0]->REQVEH;
        
                //Se almacena el nombre de la imagen, que es la placa de la móvil
                $imagen_nombre = $result_movil[0]->placa;
        
                //Se consigue un UUID cualquiera
                $uuid_imagen = Str::uuid();
        
                /* Se almacena en una variable la propia ruta de la imagen, en dónde se va a almacenar y que nombre
                    va a tener. */
                $imagen_ruta = 'danos_movil/' . $nombre_carpeta . '/' . $imagen_nombre . '_' . $uuid_imagen . '.png';
        
                // Se junta la ruta con la imagen ya decodificada, y se guarda en el almacenamiento
                Storage::put($imagen_ruta, base64_decode($imagen));
            }

            else {

                //Si no hay alguna imagen, la ruta se vuelve nulo
                $imagen_ruta = null;
            }

            //Se ejecuta la query con los valores respectivos
            $result_insert_bitacora = DB::connection()->select(DB::raw($query_insert_bitacora), [
                $fields["id_turno"],
                $result_aperturas[0]->REQVEH,
                $result_movil[0]->placa,
                $result_aperturas[0]->REQENF,
                $fields["danos_automotor"],
                $imagen_ruta,
                $fields["comentarios_auxiliar"],
            ]);

            //Una vez ejecutada la query, recoge su id y la devuelve como respuesta
            return DB::getPdo()->lastInsertId();
        }

        //En cambio, si es un conductor
        else if ($result_usuario[0]->cargo == 'Conductor') {

            //Recoge los datos del móvil referenciado en las aperturas
            $query_movil = "SELECT placa FROM equipos WHERE VEHCOD = ?";

            $result_movil = DB::connection()->select(DB::raw($query_movil), [$result_aperturas[0]->REQVEH]);
            
            //Hace una query para insertar en la bitácora
            $query_insert_bitacora = "INSERT INTO entrega_turnos_bitacora (id_turno, id_movil, placa, 
            id_conductor, danos_automotor, foto_automotor, comentarios_conductor) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        
            //Verifica si el string de base64 existe o no para empezar la operación
            if ($fields["foto_automotor"] !== null || $fields["foto_automotor"] !== "") {

                // Se almacena el string en base64 de la foto de la móvil
                $imagen = $fields["foto_automotor"];
        
                //Se reemplaza el identificador al inicio del texto por un string vacío
                $imagen = str_replace('data:image/png;base64', '', $imagen);
        
                //Se reemplaza ese espacio por un caracter
                $imagen = str_replace(' ', '+', $imagen);

                $nombre_carpeta = $result_aperturas[0]->REQVEH;
        
                //Se almacena el nombre de la imagen, que es la placa de la móvil
                $imagen_nombre = $result_movil[0]->placa;
        
                //Se consigue un UUID cualquiera
                $uuid_imagen = Str::uuid();
        
                /* Se almacena en una variable la propia ruta de la imagen, en dónde se va a almacenar y que nombre
                    va a tener. */
                $imagen_ruta = 'danos_movil/' . $nombre_carpeta . '/' . $imagen_nombre . '_' . $uuid_imagen . '.png';
        
                // Se junta la ruta con la imagen ya decodificada, y se guarda en el almacenamiento
                Storage::put($imagen_ruta, base64_decode($imagen));
            }

            else {

                //Si no hay ningún string de base64, se asigna un valor nulo
                $imagen_ruta = null;
            }

            //Se ejecuta la query con los valores respectivos
            $result_insert_bitacora = DB::connection()->select(DB::raw($query_insert_bitacora), [
                $fields["id_turno"],
                $result_aperturas[0]->REQVEH,
                $result_movil[0]->placa,
                $result_aperturas[0]->REQCON,
                $fields["danos_automotor"],
                $imagen_ruta,
                $fields["comentarios_conductor"],
            ]);

            //Devuelve el último ID de la última query que ejecutó
            return DB::getPdo()->lastInsertId();
        }

        else {

            //Devuelve un error si el rol no es ni de conductor ni auxiliar
            return response(json_encode([
                'mensaje_error' => "No se pudieron guardar los datos."
            ]));
        }
    }

    public function getEquiposConCarga(Request $request) {
        $query_equipos_carga = "SELECT id_verificacion_tipo, id_bitacora, carga_inicial FROM 
        entrega_turnos_verificacion_bitacora WHERE id_bitacora = ? AND carga_inicial IS NOT NULL";

        $result_equipos_carga = DB::connection()->select(DB::raw($query_equipos_carga), [
            $request->id
        ]);

        return $result_equipos_carga;
    }
}
