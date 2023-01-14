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

        $validar_comentarios = 0;

        /* Validaciones que comprueban si cada campo cumple con las debidas reglas descritas aquí abajo: */ 
        $fields = $request->validate([
            'id_verificacion_tipo' => 'integer', //Comprueba si los caracteres especificados en la expresión regular coinciden con el valor
            'id_estado_verificacion' => 'integer',
            'comentarios' => 'nullable|string', //Comprueba si es una cadena de texto
            'valor' => 'nullable|integer',
            'carga' => 'nullable|integer|max:3'
        ]);

        /* Se recorre cada posición del objeto Request y se hace lo siguiente: */
           for ($index = 0; $index < count($request->all()); $index++) { 

               /* Por cada posición, hace una query para insertar */
               $query_insert = "INSERT INTO entrega_turnos_verificacion_bitacora 
               (id_bitacora, id_verificacion_tipo, id_categoria_verificacion, id_estado_verificacion, hay_comentarios, comentarios, valor, carga_inicial)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

               /* Si el índice existe, la query se ejecuta tomando todos los valores existentes en
                  la posición actual */
               if (isset($request[$index])) {
                   DB::connection()->select(DB::raw($query_insert), [
                    $request[$index]["id_bitacora_turnos"], 
                    $request[$index]["id_tipo_verificacion"],
                    $request[$index]["id_categoria_verificacion"],
                    $request[$index]["id_estado_verificacion"],
                    $request[$index]["activarComentario"], 
                    $request[$index]["comentarios"],
                    $request[$index]["valor"],
                    $request[$index]["carga"]
                    ]);

                    if ($request[$index]["activarComentario"] !== 0 && $request[$index]["comentarios"] !== null) {
                        $validar_comentarios = 1;
                    }
               }

               /* Si no existe, se devuelve un mensaje de error en conjunto con el índice que está fallando */
               else {
                   echo("Error en el índice" . $index);
               }

               if ($validar_comentarios == 1) {
                $query_actualizar_novedades = "UPDATE entrega_turnos_bitacora SET novedades_formulario = 1, 
                estado_novedades = 0, estado_auditoria = 0 WHERE id_bitacora = ?";

                $result_actualizar_novedades = DB::connection()->select(DB::raw($query_actualizar_novedades), [
                    $request[0]["id_bitacora_turnos"]
                ]);
               }
               else if ($validar_comentarios == 0) {
                $query_actualizar_novedades = "UPDATE entrega_turnos_bitacora SET 
                estado_novedades = 2, estado_auditoria = 2 WHERE id_bitacora = ?";

                $result_actualizar_novedades = DB::connection()->select(DB::raw($query_actualizar_novedades), [
                    $request[0]["id_bitacora_turnos"]
                ]);
               }
           }
    }

    public function getFechasProxCambio ($id_movil) {
        if (isset($id_movil)) {
            $query_prox_cambio_hidraulica = "SELECT fecha_proximo_cambio, kilometraje_proximo_cambio 
            FROM movil_cambios_aceite_hidraulico WHERE id_equipo = ? ORDER BY id_cambio_aceite_hidraulico DESC LIMIT 1";

            $result_prox_cambio_hidraulica = DB::connection()->select(DB::raw($query_prox_cambio_hidraulica), [
                $id_movil
            ]);

            if (count($result_prox_cambio_hidraulica) == 0) {
                array_push($result_prox_cambio_hidraulica, [
                    "fecha_proximo_cambio" => '--',
                    "kilometraje_proximo_cambio" => '--'
                ]);
            }

            $query_prox_cambio_aceite = "SELECT fecha_proximo_cambio, kilometraje_proximo_cambio FROM movil_cambios_aceite_motor 
            WHERE id_equipo = ? ORDER BY id_cambio_aceite_motor DESC LIMIT 1";

            $result_prox_cambio_aceite = DB::connection()->select(DB::raw($query_prox_cambio_aceite), [
                $id_movil
            ]);

            if (count($result_prox_cambio_aceite) == 0) {
                array_push($result_prox_cambio_aceite, [
                    "fecha_proximo_cambio" => '--',
                    "kilometraje_proximo_cambio" => '--'
                ]);
            }

            $query_prox_cambio_frenos = "SELECT fecha_proximo_cambio, kilometraje_proximo_cambio FROM movil_cambios_frenos WHERE 
            id_equipo = ? ORDER BY id_cambio_frenos DESC LIMIT 1";

            $result_prox_cambio_frenos = DB::connection()->select(DB::raw($query_prox_cambio_frenos), [
                $id_movil
            ]);

            if (count($result_prox_cambio_frenos) == 0) {
                array_push($result_prox_cambio_frenos, [
                    "fecha_proximo_cambio" => '--',
                    "kilometraje_proximo_cambio" => '--'
                ]);
            }

            $query_prox_cambio_suspension = "SELECT fecha_proximo_cambio, kilometraje_proximo_cambio FROM movil_cambios_suspension 
            WHERE id_equipo = ? ORDER BY id_cambio_suspension DESC LIMIT 1";

            $result_prox_cambio_suspension = DB::connection()->select(DB::raw($query_prox_cambio_suspension), [
                $id_movil
            ]);

            if (count($result_prox_cambio_suspension) == 0) {
                array_push($result_prox_cambio_suspension, [
                    "fecha_proximo_cambio" => '--',
                    "kilometraje_proximo_cambio" => '--'
                ]);
            }

            return response(json_encode([
                "prox_cambio_hidraulica" => $result_prox_cambio_hidraulica,
                "prox_cambio_aceite" => $result_prox_cambio_aceite,
                "prox_cambio_frenos" => $result_prox_cambio_frenos,
                "prox_cambio_suspension" => $result_prox_cambio_suspension
            ]));
        }
    }

    public function getAmbulanceData ($id_movil) {

        if (isset($id_movil)) {
            /* Consigue la fecha de vencimiento del último soat registrado de la móvil */
            $query_soat = "SELECT fecha_vencimiento FROM movil_soat WHERE id_equipo = ? 
            ORDER BY id_soat DESC LIMIT 1";

            $result_soat = DB::connection()->select(DB::raw($query_soat), [
                $id_movil
            ]);

            if (count($result_soat) == 0) {
                array_push($result_soat, [
                    "fecha_vencimiento" => '--'
                ]);
            }

            /* Consigue la última fecha de vencimiento del extintor de la móvil. Si la móvil es una ambulancia se requiere las fechas
            de vencimiento de ambos extintores. */
            $query_extintores = "SELECT fecha_vencimiento_extintor1 AS fecha_vencimiento FROM movil_extintores WHERE id_equipo = ? 
            ORDER BY id_extintor DESC LIMIT 1";

            $result_extintores = DB::connection()->select(DB::raw($query_extintores), [
                $id_movil
            ]);

            if (count($result_extintores) == 0) {
               array_push($result_extintores, [
                "fecha_vencimiento" => '--'
               ]);
            }

            $query_segundo_extintor = "SELECT fecha_vencimiento_extintor2 AS fecha_vencimiento FROM movil_extintores WHERE id_equipo = ? 
            ORDER BY id_extintor DESC LIMIT 1";

            $result_segundo_extintor = DB::connection()->select(DB::raw($query_segundo_extintor), [
                $id_movil
            ]);

            if (count($result_extintores) == 0) {
                array_push($result_segundo_extintor, [
                    "fecha_vencimiento" => '--'
                ]);
            }

            if ($result_segundo_extintor[0]->fecha_vencimiento == null) {
                $result_segundo_extintor[0]->fecha_vencimiento = '--';
            }


            /* Consigue la última revisión tecnicomecánica en este móvil */
            $query_tecnomecanica = "SELECT fecha_revision FROM movil_tecnomecanica WHERE id_equipo = ? 
            ORDER BY id_tecnomecanica DESC LIMIT 1";

            $result_tecnomecanica = DB::connection()->select(DB::raw($query_tecnomecanica), [
                $id_movil
            ]);

            if (count($result_tecnomecanica) == 0) {
                array_push($result_tecnomecanica, [
                    "fecha_revision" => '--'
                ]);
            }

            /* Consigue el último cambio de hidráulica de la móvil */
            $query_cambios_hidraulica = "SELECT fecha_ultimo_cambio, kilometraje_ultimo_cambio FROM movil_cambios_aceite_hidraulico 
            WHERE id_equipo = ? ORDER BY id_cambio_aceite_hidraulico DESC LIMIT 1";

            $result_cambios_hidraulica = DB::connection()->select(DB::raw($query_cambios_hidraulica), [
                $id_movil
            ]);

            if (count($result_cambios_hidraulica) == 0) {
                array_push($result_cambios_hidraulica, [
                    "fecha_ultimo_cambio" => '--',
                    "kilometraje_ultimo_cambio" => '--'
                ]);
            }

            /* Consigue el último cambio de aceite realizado en la móvil */
            $query_cambios_aceite = "SELECT fecha_ultimo_cambio, kilometraje_ultimo_cambio FROM movil_cambios_aceite_motor 
            WHERE id_equipo = ? ORDER BY id_cambio_aceite_motor DESC LIMIT 1";

            $result_cambios_aceite = DB::connection()->select(DB::raw($query_cambios_aceite), [
                $id_movil
            ]);

            if (count($result_cambios_aceite) == 0) {
                array_push($result_cambios_aceite, [
                    "fecha_ultimo_cambio" => '--',
                    "kilometraje_ultimo_cambio" => '--'
                ]);
            }

            /* Consigue el último cambio de frenos de la móvil */
            $query_cambios_frenos = "SELECT fecha_ultimo_cambio, kilometraje_ultimo_cambio FROM movil_cambios_frenos
            WHERE id_equipo = ? ORDER BY id_cambio_frenos DESC LIMIT 1";

            $result_cambios_frenos = DB::connection()->select(DB::raw($query_cambios_frenos), [
                $id_movil
            ]);

            if (count($result_cambios_frenos) == 0) {
                array_push($result_cambios_frenos, [
                    "fecha_ultimo_cambio" => '--',
                    "kilometraje_ultimo_cambio" => '--'
                ]);
            }

            /* Consigue el último cambio de suspensión de la móvil */
            $query_cambios_suspension = "SELECT fecha_ultimo_cambio, kilometraje_ultimo_cambio FROM movil_cambios_suspension
            WHERE id_equipo = ? ORDER BY id_cambio_suspension DESC LIMIT 1"; 

            $result_cambios_suspension = DB::connection()->select(DB::raw($query_cambios_suspension), [
                $id_movil
            ]);

            if (count($result_cambios_suspension) == 0) {
                array_push($result_cambios_suspension, [
                    "fecha_ultimo_cambio" => '--',
                    "kilometraje_ultimo_cambio" => '--'
                ]);
            }

            /* Devuelve todas las respuestas agrupadas en un JSON*/
                return response(json_encode([
                    "fecha_soat" => $result_soat,
                    "fecha_extintor" => $result_extintores,
                    "fecha_segundo_extintor" => $result_segundo_extintor,
                    "revision_tecno" => $result_tecnomecanica,
                    "cambios_hidraulica" => $result_cambios_hidraulica,
                    "cambios_aceite" => $result_cambios_aceite,
                    "cambios_frenos" => $result_cambios_frenos,
                    "cambios_suspension" => $result_cambios_suspension
                ]));
            }
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

            $query_ultima_bitacora = "SELECT * FROM entrega_turnos_bitacora ORDER BY fecha_registro 
            DESC LIMIT 1";

            $result_ultima_bitacora = DB::connection()->select(DB::raw($query_ultima_bitacora));

            //Devuelve el registro de la última query que ejecutó
            return $result_ultima_bitacora;
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

            $query_ultima_bitacora = "SELECT * FROM entrega_turnos_bitacora ORDER BY fecha_registro 
            DESC LIMIT 1";

            $result_ultima_bitacora = DB::connection()->select(DB::raw($query_ultima_bitacora));

            //Devuelve el registro de la última query que ejecutó
            return $result_ultima_bitacora;
        }

        else {

            //Devuelve un error si el rol no es ni de conductor ni auxiliar
            return response(json_encode([
                'mensaje_error' => "No se pudieron guardar los datos."
            ]));
        }
    }

    /* Consigue aquellos equipos médicos que requieran carga, los cuales se pueden distinguir de entre
    los demás si su carga inicial no es nula, además se usa el ID de la bitácora proveniente de un request */
    public function getEquiposConCarga(Request $request) {
        $query_equipos_carga = "SELECT id_verificacion_tipo, id_bitacora, carga_inicial FROM 
        entrega_turnos_verificacion_bitacora WHERE id_bitacora = ? AND carga_inicial IS NOT NULL";

        $result_equipos_carga = DB::connection()->select(DB::raw($query_equipos_carga), [
            $request->id
        ]);

        return $result_equipos_carga;
    }

    /* Asigna las cargas finales a la bitácora dependiendo de su ID de bitácora y de la verificación. */
    public function setCargasFinales(Request $request) {

        $query_cargas_finales = "UPDATE entrega_turnos_verificacion_bitacora SET carga_final = ? WHERE id_bitacora = ? AND id_verificacion_tipo = ?";

        /* Recorre cada dispositivo con carga final y lo va asignando */
        foreach($request->all() as $carga) {
            $result_cargas_finales = DB::connection()->select(DB::raw($query_cargas_finales), [
                $carga["carga_final"],
                $carga["id_bitacora"],
                $carga["id_metodo"]
            ]);
        }

        /* Actualiza la bandera indicando que el formulario ya se llenó */
        $query_actualizacion_bandera = "UPDATE entrega_turnos_bitacora SET formulario_cargas_llenado = 1 WHERE id_bitacora = ?";

        $result_actualizacion_bandera = DB::connection()->select(DB::raw($query_actualizacion_bandera), [
            $carga["id_bitacora"]
        ]);
    }

    /* Consigue una config para determinar el nivel de batería que debe estar el equipo médico para que 
    sea recomendable cargarlo. */
    public function getConfigs() {
        $query_configs = "SELECT `value` FROM configs WHERE `key` LIKE 'entrega_turnos_carga_equipos' LIMIT 1";

        $result_configs = DB::connection()->select(DB::raw($query_configs));

        return $result_configs;
    }

    public function getConfigsValidacionCambio() {
        $query_configs_validacion = "SELECT `value` FROM configs WHERE `key` LIKE 'entrega_turnos_formulario%'";

        $result_configs_validacion = DB::connection()->select(DB::raw($query_configs_validacion));

        return $result_configs_validacion;
    }

    /* Consigue los tipos de productos para aseo */
    public function getTiposProductosAseo() {
        $query_tipos_productos = "SELECT * FROM entrega_turnos_tipos_productos_aseo";

        $result_tipos_productos = DB::connection()->select(DB::raw($query_tipos_productos));

        return $result_tipos_productos;
    }

    /* Consigue todos los productos de aseo que no estén inactivos */
    public function getProductosAseo() {
        $query_productos_aseo = "SELECT id_producto_aseo, producto, tipo_producto 
        FROM entrega_turnos_productos_aseo WHERE estado = 1";

        $result_productos_aseo = DB::connection()->select(DB::raw($query_productos_aseo));

        return $result_productos_aseo;
    }

    /* Envía el formulario de aseo y desinfección, guardando los datos en una bitácora */
    public function enviarFormularioAseo(Request $request) {
        $query_bitacora_aseo = "INSERT INTO entrega_turnos_aseo_bitacora 
        (id_bitacora, id_tipo_producto, id_producto_aseo, utilizado) VALUES (?, ?, ?, ?)";

        /* Recorre las respuestas, una a una, y va insertando */
        foreach($request->all() as $formulario) {
            $result_bitacora_aseo = DB::connection()->select(DB::raw($query_bitacora_aseo), [
                $formulario["id_bitacora"],
                $formulario["id_tipo"],
                $formulario["id_producto"],
                $formulario["utilizado"]
            ]);
        }

        /* Actualiza la bandera de aseo terminal, indicando al usuario que ese formulario ya fue llenado */
        $query_bandera_aseo = "UPDATE entrega_turnos_bitacora SET aseo_terminal = 1 WHERE id_bitacora = ?";

        $result_bandera_aseo = DB::connection()->select(DB::raw($query_bandera_aseo), [
            $formulario["id_bitacora"]
        ]);
    }

    /* Valida la jornada del formulario de temperatura de la siguiente manera: cuando haya un registro en
    bitácora con el mismo ID de móvil y efectuado en la misma fecha, dicha jornada se pasa al de la tarde,
    en cambio si no hay ninguno se pasa al de la mañana. */
    public function validarJornada(Request $request) {
        $query_validacion_jornada = "SELECT id_control_temperatura FROM entrega_turnos_control_temperatura
        WHERE id_movil = ? AND fecha_registro >= ? LIMIT 1";

        /* Efectúa la query con la ID de la móvil y la fecha de hoy en formato yyyy-mm-dd */
        $result_validacion_jornada = DB::connection()->select(DB::raw($query_validacion_jornada), [
            $request->id_movil,
            date("Y-m-d")
        ]);

        /* Si no encuentra ninguno, retorna 0, de lo contrario 1 */
        if (count($result_validacion_jornada) == 0) {
            return 0;
        }

        else {
            return 1;
        }
    }

    /* Envía el formulario de temperaturas, de acuerdo con lo diligenciado en el formulario */
    public function enviarFormularioTemperatura(Request $request) {
        $query_insert_temperatura = "INSERT INTO entrega_turnos_control_temperatura 
        (id_bitacora, id_movil, temperatura_normal, temperatura_max, temperatura_min, humedad, jornada)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $result_insert_temperatura = DB::connection()->select(DB::raw($query_insert_temperatura), [
            $request->id_bitacora,
            $request->id_movil,
            $request->temp_normal,
            $request->temp_max,
            $request->temp_min,
            $request->humedad_max,
            isset($request->jornada)? $request->jornada : '1'
        ]);

        /* Actualiza la bandera indicando que el formulario ya se llenó */
        $query_bandera_temperatura = "UPDATE entrega_turnos_bitacora SET formulario_temperatura_llenado = 1 WHERE id_bitacora = ?";

        $result_bandera_temperatura = DB::connection()->select(DB::raw($query_bandera_temperatura), [
            $request->id_bitacora
        ]);
    }

    /* Inserta una novedad cada vez que se presenta una al enviar el formulario. Mientras procesa la información, si una verificación
    del formulario tiene algún comentario se añade aquí también. */
    public function insertarNovedad(Request $request) {
        $query_insert_novedad = "INSERT INTO entrega_turnos_novedades_bitacora 
        (id_bitacora, id_turno, id_movil, id_auxiliar, id_conductor, id_verificacion_tipo, id_categoria_verificacion, comentarios_novedad) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        foreach ($request->all() as $novedad) {
            $result_insert_novedad = DB::connection()->select(DB::raw($query_insert_novedad), [
                $novedad["id_bitacora"],
                $novedad["id_turno"],
                $novedad["id_movil"],
                $novedad["id_auxiliar"],
                $novedad["id_conductor"],
                $novedad["id_verificacion_tipo"],
                $novedad["id_categoria_verificacion"],
                $novedad["comentarios_novedad"]
            ]);
        }
    }

    public function getNovedadesMovil(Request $request) {
        $query_encontrar_novedades = "SELECT DISTINCT id_verificacion_tipo, comentarios_novedad, fecha_creacion FROM 
        entrega_turnos_novedades_bitacora WHERE estado_auditoria = 0 AND id_movil = ?";

        $result_encontrar_novedades = DB::connection()->select(DB::raw($query_encontrar_novedades), [
            $request->id_movil
        ]);

        return $result_encontrar_novedades;
    }
}
