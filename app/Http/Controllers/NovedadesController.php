<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\NovedadesExport;
use App\Exports\AuditoriasExport;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;


class NovedadesController extends Controller {
    
    /* Consigue todas las novedades de todos los formularios que no estén revisadas, 
    y las ordena por categoría de verificación */
    public function getNovedades() {
        $query_novedades = "SELECT * FROM entrega_turnos_novedades_bitacora ORDER BY id_categoria_verificacion";

        $result_novedades = DB::connection()->select(DB::raw($query_novedades));

        return $result_novedades;
    }

    /* Función que consigue las novedades ordenadas por móvil y cuyo estado de auditoría no esté revisado
    o aprobado.*/
    public function getNovedadesAuditoria() {
        $query_novedades_auditoria = "SELECT * FROM entrega_turnos_novedades_bitacora ORDER BY id_movil";

        $result_novedades_auditoria = DB::connection()->select(DB::raw($query_novedades_auditoria));

        return $result_novedades_auditoria;
    }

    /* Consigue las verificaciones con las cuales se trabajan en el formulario. Esto con el propósito de añadir legiblidad en donde
    se va a utilizar */
    public function getVerificacionesNovedades() {
        $query_verificaciones = "SELECT id_verificacion_tipo, tipo_verificacion, id_categoria_verificacion
        FROM entrega_turnos_verificacion_tipo WHERE estado = 1";

        $result_verificaciones = DB::connection()->select(DB::raw($query_verificaciones));

        return $result_verificaciones;
    }

    /* Consigue el nombre del turno el cual fue asignado anteriormente en htabajadas. Consiguiendo el ID del turno en htrabajadas
    podemos discernir el nombre del turno y traerlo */
    public function findTurno(Request $request) {
        $query_htrabajadas = "SELECT Turno FROM htrabajadas WHERE Id_Hora = ? LIMIT 1"; // Consigue el ID del turno en htrabajadas

        $result_htrabajadas = DB::connection()->select(DB::raw($query_htrabajadas), [
            $request->data
        ]);

        foreach ($result_htrabajadas as $turno) {
            $query_turnos = "SELECT Turno FROM turnos WHERE id_Turno = ?"; // Consigue el nombre del turno con base al ID de turno anterior

            $result_turnos = DB::connection()->select(DB::raw($query_turnos), [
                $turno->Turno
            ]);
        }

        return $result_turnos;
    }

    /* Envía los datos del cambio de revisión. Estos datos se envían tanto a la bitácora de novedades, en donde se actualiza, y
    a una bitácora de cambios en donde se registra el cambio realizado. */
    public function enviarDatosCambio(Request $request) {

        $ruta_imagen = null;

        $query_actualizacion_bitacora = ""; //Se inicializa un string de query

        /* Si el estado nuevo es "revisando", actualiza el registro en bitácora con su estado nuevo, la fecha de su última revisión,
        la nota que es opcional y un archivo adjunto el cual también es opcional. */
        if ($request->estado_nuevo == 1) {
            $query_actualizacion_bitacora = "UPDATE entrega_turnos_novedades_bitacora SET 
            estado_revision = ?, fecha_revisando = ?, nota_revision = ? WHERE id_novedad = ?";

            $result_actualizacion_bitacora = DB::connection()->select(DB::raw($query_actualizacion_bitacora), [
                $request->estado_nuevo,
                NOW(),
                $request->nota_revision,
                $request->id_novedad
            ]);

            $query_actualizar_auditoria = "UPDATE entrega_turnos_novedades_bitacora SET
            estado_auditoria = 0 WHERE id_novedad = ?";

            $result_actualizar_auditoria = DB::connection()->select(DB::raw($query_actualizar_auditoria), [
                $request->id_novedad
            ]);

            /* Luego se agrega el cambio en la bitácora de cambios, en donde se registra su estado antiguo y su estado nuevo, 
            la nota de revisión y su archivo adjunto, el cual son opcionales. */
            $query_agregar_cambio = "INSERT INTO entrega_turnos_novedades_bitacora_cambios 
            (id_novedad, id_autor_cambio, revision_antes, revision_despues, comentarios, imagen_adjunta) 
            VALUES (?, ?, ?, ?, ?, ?)";

            if ($request->hasFile('archivo_adjunto') || $request->imagen_adjunta !== null) {
                
                $archivo = $request->file('archivo_adjunto');

                $nombre_archivo = $archivo->getClientOriginalName();

                $fecha_carpeta = date("Y-m-d");

                $ruta_imagen = 'archivos_cambio_revision/' . $request->id_novedad . 
                '_' . $fecha_carpeta . '/' . $nombre_archivo;

                Storage::disk('local')->put($ruta_imagen, File::get($archivo));
            }

            $result_agregar_cambio = DB::connection()->select(DB::raw($query_agregar_cambio), [
                $request->id_novedad,
                auth()->user()->Id,
                $request->estado_antiguo,
                $request->estado_nuevo,
                $request->nota_revision,
                $ruta_imagen
            ]);
        }
        
        /* En cambio, si el estado es 'revisado', el único cambio que se hace es que guarda la última fecha de revisión en su respectivo
        campo. */
        else if ($request->estado_nuevo == 2) {
            $query_actualizacion_bitacora = "UPDATE entrega_turnos_novedades_bitacora SET 
            estado_revision = ?, fecha_revision = ?, nota_revision = ? WHERE id_novedad = ?";

            $result_actualizacion_bitacora = DB::connection()->select(DB::raw($query_actualizacion_bitacora), [
                $request->estado_nuevo,
                NOW(),
                $request->nota_revision,
                $request->id_novedad
            ]);

            $query_actualizar_auditoria = "UPDATE entrega_turnos_novedades_bitacora SET
            estado_auditoria = 0 WHERE id_novedad = ?";

            $result_actualizar_auditoria = DB::connection()->select(DB::raw($query_actualizar_auditoria), [
                $request->id_novedad
            ]);

            $query_agregar_cambio = "INSERT INTO entrega_turnos_novedades_bitacora_cambios 
            (id_novedad, id_autor_cambio, revision_antes, revision_despues, comentarios, imagen_adjunta) 
            VALUES (?, ?, ?, ?, ?, ?)";

            if ($request->hasFile('archivo_adjunto') || $request->imagen_adjunta !== null) {
                $archivo = $request->file('archivo_adjunto');

                $nombre_archivo = $archivo->getClientOriginalName();

                $fecha_carpeta = date("Y-m-d");

                $ruta_imagen = 'archivos_cambio_revision/' . $request->id_novedad . 
                '_' . $fecha_carpeta . '/' . $nombre_archivo;

                Storage::disk('local')->put($ruta_imagen, File::get($archivo));
            }

            $result_agregar_cambio = DB::connection()->select(DB::raw($query_agregar_cambio), [
                $request->id_novedad,
                auth()->user()->Id,
                $request->estado_antiguo,
                $request->estado_nuevo,
                $request->nota_revision,
                $ruta_imagen
            ]);
        }
    }

    /* Consigue las categorías de cada verificación, utilizados durante el formulario de entrega de turnos. */
    public function getCategoriasNovedad() {
        $query_categorias = "SELECT * FROM entrega_turnos_categoria_verificacion";

        $result_categorias = DB::connection()->select(DB::raw($query_categorias));

        return $result_categorias;
    }

    /* Consigue la lista de equipos disponibles. */
    public function getMovilesNovedad() {
        $query_moviles = "SELECT VEHNOM AS nombre, ID_Equipo AS id_equipo FROM equipos";

        $result_moviles = DB::connection()->select(DB::raw($query_moviles));

        return $result_moviles;
    }

    /* Filtra registros mediante un concatenado de queries. Si un parámetro por el cual vamos a filtrar existe, se añade ese
    fragmento de query. Luego se envía la petición una vez todas las condicionales hayan sido evaluadas. */
    public function filtro(Request $request) {
        $query_base_buscar_novedades = "SELECT * FROM entrega_turnos_novedades_bitacora WHERE "; //Se instancia una query base, que seleccionará todos los campos de la bitácora de novedades
        $query_base_categorias = null; //Se instancia una query base en caso de que se quiera buscar por una categoría específica.

        $result_buscar_novedades = null;

        /* Si existen tanto fecha inicial como final en la petición, se añade el pedazo de query a la query base.*/
        if (isset($request->fecha_inicial) && isset($request->fecha_final)) {
            $query_base_buscar_novedades .= "fecha_creacion >=" .
            "'" . $request->fecha_inicial . " 00:00:00" . "'" . " AND fecha_creacion <= " . "'" . $request->fecha_final . " 23:59:59" . "'";
        }

        /* Si existe un parámetro para buscar por móvil, se añade el trozo de query a la query base */
        if (isset($request->movil)) {
            $query_base_buscar_novedades .= " AND id_movil = " . $request->movil;
        }

        /* Si existe un parámetro para buscar por estado de revisión, se añade el fragmento a la query
        base. */
        if (isset($request->estado)) {
            $query_base_buscar_novedades .= " AND estado_revision = " . $request->estado;
        }

        /* Si existe un parámetro para buscar por estado de auditoría, se añade el fragmento a la query base. */
        if (isset($request->estado_auditoria)) {
            $query_base_buscar_novedades .= " AND estado_auditoria = " . $request->estado_auditoria;
        }

        /* Si se quiere buscar por categoría, se añade para buscar por una categoría específica a la query base. */
        if (isset($request->categoria)) {
            $query_base_buscar_novedades .= " AND id_categoria_verificacion = " . $request->categoria . " ORDER BY id_categoria_verificacion";
        }
        
        /* Se ejecuta la query cuando todos los parámetros estén validados */
        $result_buscar_novedades = DB::connection()->select(DB::raw($query_base_buscar_novedades));

        return $result_buscar_novedades;
    }

    /* Función la cual se exportan los datos que se traen por petición a un .xlsx, un documento de Excel, ideal para reportes */
    public function exportarDatos(Request $request) {

        $fecha_archivo = date('Y-m-d_H:i:s'); //Se instancia un date en formato yyyy-mm-dd hh:mm:ss

        $array_datos = []; //Se instancia un array de datos vacío

        /* Se recorre cada elemento de la petición y luego se añade al array vacío instanciado anteriormente */
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

        /* Se llama al constructor del exportador y se pasa el array de datos */
        $datos_tabla = new NovedadesExport($array_datos);

        /* Devuelve un descargable, junto con los datos de la tabla y el nombre del archivo */
        return Excel::download($datos_tabla, 'reporte_novedades_' . $fecha_archivo . '.xlsx');
    }

    /* Función que permite actualizar la novedad para que tenga una fecha, estado y nota de auditoría. 
    También guarda el cambio efectuado en la bitácora de cambios. */
    public function insertarAuditoria(Request $request) {

        /* Actualiza el registro el cual está la novedad para que tenga una fecha, estado y nota de 
        auditoría definidos. */
        $query_insert_auditoria = "UPDATE entrega_turnos_novedades_bitacora SET 
        estado_auditoria = ?, nota_auditoria = ?, fecha_auditoria = ? WHERE id_novedad = ?";

        $result_insert_auditoria = DB::connection()->select(DB::raw($query_insert_auditoria), [
            $request->auditoria,
            $request->comentarios,
            now(),
            $request->id_novedad
        ]);

        /* Si la auditoría fue efectuada pero la revisión no fue aprobada, se cambia el estado de revisión
        a "Auditado, rechazado", para que el que revisó la novedad pueda revisar otra vez. */
        if ($request->auditoria == 2) {
            $query_actualizar_estado = "UPDATE entrega_turnos_novedades_bitacora SET
            estado_revision = 3 WHERE id_novedad = ?";

            $result_actualizar_estado = DB::connection()->select(DB::raw($query_actualizar_estado), [
                $request->id_novedad
            ]);
        }

        /* Inserta el cambio efectuado en una bitácora de cambios, el cual lleva un recuento del cambio
        hecho y quién hizo tal cambio. */
        $query_insert_cambio = "INSERT INTO entrega_turnos_bitacora_cambios_auditoria 
        (id_novedad, estado_auditoria_nuevo, comentarios_auditoria, id_autor_cambio) 
        VALUES (?, ?, ?, ?)";

        $result_insert_cambio = DB::connection()->select(DB::raw($query_insert_cambio), [
            $request->id_novedad,
            $request->auditoria,
            $request->comentarios,
            auth()->user()->Id
        ]);


    }

    /* Función que permite filtrar registros en el módulo de auditorías mediante un concatenado de queries. Si uno de los parámetros
    en la petición contiene un valor, se añade el fragmento de la query respectiva. */
    public function filtroAuditorias(Request $request) {
        $query_base = "SELECT * FROM entrega_turnos_novedades_bitacora WHERE ";

        $result_query_base = null;
        
        /* Si existen parámetros para determinar un rango de fechas, se añade el fragmento respectivo a la query base. */
        if (isset($request->fecha_inicial) && isset($request->fecha_final)) {
            $query_base .= "fecha_creacion >=" .
            "'" . $request->fecha_inicial . " 00:00:00" . "'" . " AND fecha_creacion <= " . "'" . $request->fecha_final . " 23:59:59" . "'";
        }

        /* Si existe un parámetro para buscar por móvil, se añade el trozo de query a la query base */
        if (isset($request->movil)) {
            $query_base .= " AND id_movil = " . $request->movil;
        }

        /* Si existe un parámetro para buscar por estado de revisión, se añade el fragmento a la query
        base. */
        if (isset($request->estado_novedad)) {
            $query_base .= " AND estado_revision = " . $request->estado_novedad;
        }

        /* Si existe un parámetro para buscar por estado de auditoría, se añade el fragmento a la query base. */
        if (isset($request->estado_auditoria)) {
            $query_base .= " AND estado_auditoria = " . $request->estado_auditoria;
        }

        /* Si se quiere buscar por categoría, se añade para buscar por una categoría específica a la query base. */
        if (isset($request->categoria)) {
            $query_base .= " AND id_categoria_verificacion = " . $request->categoria . " ORDER BY id_categoria_verificacion";
        }

        /* Finalmente se ejecuta la query */
        $result_query_base = DB::connection()->select(DB::raw($query_base));

        return $result_query_base;
    }

    /* Función que permite exportar las auditorías actuales a un documento de Excel, ideal para reportes
    y demás. */
    public function exportarAuditorias(Request $request) {
        $fecha_archivo = date('Y-m-d_H:i:s'); //Se instancia un date en formato yyyy-mm-dd hh:mm:ss

        $array_datos = []; //Se instancia un array de datos vacío

        /* Se recorre cada elemento de la petición y luego se añade al array vacío instanciado anteriormente */
        foreach ($request->all() as $auditoria) {
            array_push($array_datos, [
                $auditoria["id_movil"],
                $auditoria["id_turno"],
                $auditoria["auxiliar"],
                $auditoria["conductor"],
                $auditoria["verificacion"],
                $auditoria["categoria"],
                $auditoria["comentario_nov"],
                $auditoria["estado_revision"],
                $auditoria["nota_revision"],
                $auditoria["estado_auditoria"],
                $auditoria["nota_auditoria"],
                $auditoria["fecha_auditoria"]
            ]);
        }

        /* Se llama al constructor del exportador y se pasa el array de datos */
        $datos_tabla = new AuditoriasExport($array_datos);

        /* Devuelve un descargable, junto con los datos de la tabla y el nombre del archivo */
        return Excel::download($datos_tabla, 'reporte_auditorias' . $fecha_archivo . '.xlsx');
    }

    public function validarCantidadNovedad(Request $request) {
        $query_validacion_novedad = "SELECT id_novedad FROM entrega_turnos_novedades_bitacora WHERE 
        id_turno = ? AND NOT estado_revision = 2";

        $result_validacion_novedad = DB::connection()->select(DB::raw($query_validacion_novedad), [
            $request->id_turno
        ]);

        if (count($result_validacion_novedad) == 0) {
            $query_actualizar_estado_novedad = "UPDATE entrega_turnos_bitacora SET 
            estado_novedades = 1 WHERE id_turno = ?";

            $result_actualizar_estado_novedad = DB::connection()->select(DB::raw($query_actualizar_estado_novedad), [
                $request->id_turno
            ]);
        }

        else if (count($result_validacion_novedad) > 0) {
            $query_actualizar_estado_novedad = "UPDATE entrega_turnos_bitacora SET 
            estado_novedades = 0 WHERE id_turno = ?";

            $result_actualizar_estado_novedad = DB::connection()->select(DB::raw($query_actualizar_estado_novedad), [
                $request->id_turno
            ]);
        }
    }

    public function validarCantidadAuditoria(Request $request) {
        $query_validacion_auditoria = "SELECT id_novedad FROM entrega_turnos_novedades_bitacora WHERE id_turno = ? AND NOT estado_auditoria = 1";

        $result_validacion_auditoria = DB::connection()->select(DB::raw($query_validacion_auditoria), [
            $request->id_turno
        ]);

        if (count($result_validacion_auditoria) == 0) {
            $query_actualizar_estado_auditoria = "UPDATE entrega_turnos_bitacora SET estado_auditoria = 1 WHERE id_turno = ?";

            $result_actualizar_estado_auditoria = DB::connection()->select(DB::raw($query_actualizar_estado_auditoria), [
                $request->id_turno
            ]);
        }

        else if (count($result_validacion_auditoria) > 0) {
            $query_actualizar_estado_auditoria = "UPDATE entrega_turnos_bitacora SET estado_auditoria = 0 WHERE id_turno = ?";

            $result_actualizar_estado_auditoria = DB::connection()->select(DB::raw($query_actualizar_estado_auditoria), [
                $request->id_turno
            ]);
        }
    }

    public function exportarNovedadPdf(Request $request) {
        $config_codigo = "";
        $config_version = "";
        $config_estandar = "";
        $config_pagina = "";
        $base64_firma = "";
        $base64_firma_auditor = "";

        $nombre_apellido_solucion = "";
        $cargo_solucion = "";
        $cedula_solucion = "";
        $nombre_apellido_auditor = "";
        $cargo_auditor = "";
        $cedula_auditor = "";

        $query_encontrar_usuario_cambio = "SELECT id_autor_cambio FROM 
        entrega_turnos_novedades_bitacora_cambios WHERE id_novedad = ? ORDER BY id_cambio DESC LIMIT 1";

        $result_encontrar_usuario_cambio = DB::connection()->select(DB::raw($query_encontrar_usuario_cambio), [
            $request->id_novedad
        ]);

        $query_firma_usuario = "SELECT nombre, documento, apellido, cargo, firma_solucion_pdf FROM usuarios_app WHERE Id = ?";

        if (count($result_encontrar_usuario_cambio) !== 0 && $result_encontrar_usuario_cambio[0]->id_autor_cambio !== null) {
            $result_firma_usuario = DB::connection()->select(DB::raw($query_firma_usuario), [
                $result_encontrar_usuario_cambio[0]->id_autor_cambio
            ]);

            if (count($result_firma_usuario) !== 0 && $result_firma_usuario[0]->firma_solucion_pdf !== null) {

                $nombre_apellido_solucion = $result_firma_usuario[0]->nombre . " " . $result_firma_usuario[0]->apellido;
                $cedula_solucion = $result_firma_usuario[0]->documento;
                $cargo_solucion = strtoupper($result_firma_usuario[0]->cargo);
                
                $ruta_firma = $result_firma_usuario[0]->firma_solucion_pdf;
    
                if (Storage::disk('local')->exists($ruta_firma)) {
                    $archivo_firma = Storage::get($ruta_firma);
    
                    $base64_firma = base64_encode($archivo_firma);
                }
            }
        }

        // --------------------------------------------------------------------

        $query_encontrar_auditor_cambio  = "SELECT id_autor_cambio FROM entrega_turnos_bitacora_cambios_auditoria WHERE id_novedad = ? 
        ORDER BY id_cambio_auditoria DESC LIMIT 1";

        $result_encontrar_auditor_cambio = DB::connection()->select(DB::raw($query_encontrar_auditor_cambio), [
            $request->id_novedad
        ]);

        $query_firma_auditor = "SELECT nombre, documento, apellido, cargo, firma_auditor_pdf FROM usuarios_app WHERE Id = ?";

        if (count($result_encontrar_auditor_cambio) !== 0 && $result_encontrar_auditor_cambio[0]->id_autor_cambio !== null) {
            $result_firma_auditor = DB::connection()->select(DB::raw($query_firma_auditor), [
                $result_encontrar_auditor_cambio[0]->id_autor_cambio
            ]);

            if (count($result_firma_auditor) !== 0 && $result_firma_auditor[0]->firma_auditor_pdf !== null) {
                $nombre_apellido_auditor = $result_firma_auditor[0]->nombre . " " . $result_firma_auditor[0]->apellido;
                $cedula_auditor = $result_firma_auditor[0]->documento;
                $cargo_auditor = strtoupper($result_firma_auditor[0]->cargo);
                $ruta_firma_auditor = $result_firma_auditor[0]->firma_auditor_pdf;
    
                if (Storage::disk('local')->exists($ruta_firma_auditor)) {
                    $archivo_firma_auditor = Storage::get($ruta_firma_auditor);
    
                    $base64_firma_auditor = base64_encode($archivo_firma_auditor);
                }
            }
        }

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

            if (str_contains($config->value, 'Pagina')) {
                $config_pagina = $config->value;
            }
        }

        $configs_json = json_encode([
            "codigo" => $config_codigo,
            "version" => $config_version,
            "estandar" => $config_estandar,
            "pagina" => $config_pagina
        ]);
        $query_placa = "SELECT placa FROM equipos WHERE ID_Equipo = ?";

        $result_placa = DB::connection()->select(DB::raw($query_placa), [
            $request->id_movil
        ]);

        $placa = json_encode($result_placa);

        $fecha_formato = date('Y-m-d');

        $descripcion_solucion = $request->nota_revision;

        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('formato_exportacion', [
            'configs_json' => $configs_json,
            'placa' => $placa,
            'fecha_formato' => $fecha_formato,
            'descripcion_solucion' => $descripcion_solucion,
            'archivo_base64' => $archivo_base64,
            'base64_firma' => $base64_firma !== ""? $base64_firma : "",
            'base64_firma_auditor' => $base64_firma_auditor !== ""? $base64_firma_auditor : "",
            'nombre_apellido_solucion' => $nombre_apellido_solucion,
            'cargo_solucion' => $cargo_solucion,
            'cedula_solucion' => $cedula_solucion,
            'nombre_apellido_auditor' => $nombre_apellido_auditor,
            'cargo_auditor' => $cargo_auditor,
            'cedula_auditor' => $cedula_auditor
        ]);

        return $pdf->download('prueba.pdf');
    }

    public function exportarNovedadPdfAuditor(Request $request) {
        $config_codigo = "";
        $config_version = "";
        $config_estandar = "";
        $config_pagina = "";

        $base64_firma = "";
        $base64_firma_auditor = "";

        $nombre_apellido_solucion = "";
        $cargo_solucion = "";
        $cedula_solucion = "";
        $nombre_apellido_auditor = "";
        $cargo_auditor = "";
        $cedula_auditor = "";

        $query_encontrar_usuario_cambio = "SELECT id_autor_cambio FROM 
        entrega_turnos_novedades_bitacora_cambios WHERE id_novedad = ? ORDER BY id_cambio DESC LIMIT 1";

        $result_encontrar_usuario_cambio = DB::connection()->select(DB::raw($query_encontrar_usuario_cambio), [
            $request->id_novedad
        ]);

        $query_firma_usuario = "SELECT nombre, documento, apellido, cargo, firma_solucion_pdf FROM usuarios_app WHERE Id = ?";

        if (count($result_encontrar_usuario_cambio) !== 0 && $result_encontrar_usuario_cambio[0]->id_autor_cambio !== null) {
            $result_firma_usuario = DB::connection()->select(DB::raw($query_firma_usuario), [
                $result_encontrar_usuario_cambio[0]->id_autor_cambio
            ]);

            if (count($result_firma_usuario) !== 0 && $result_firma_usuario[0]->firma_solucion_pdf !== null) {

                $nombre_apellido_solucion = $result_firma_usuario[0]->nombre . " " . $result_firma_usuario[0]->apellido;
                $cedula_solucion = $result_firma_usuario[0]->documento;
                $cargo_solucion = strtoupper($result_firma_usuario[0]->cargo);
                
                $ruta_firma = $result_firma_usuario[0]->firma_solucion_pdf;
    
                if (Storage::disk('local')->exists($ruta_firma)) {
                    $archivo_firma = Storage::get($ruta_firma);
    
                    $base64_firma = base64_encode($archivo_firma);
                }
            }
        }

        // --------------------------------------------------------------------

        $query_encontrar_auditor_cambio  = "SELECT id_autor_cambio FROM entrega_turnos_bitacora_cambios_auditoria WHERE id_novedad = ? 
        ORDER BY id_cambio_auditoria DESC LIMIT 1";

        $result_encontrar_auditor_cambio = DB::connection()->select(DB::raw($query_encontrar_auditor_cambio), [
            $request->id_novedad
        ]);

        $query_firma_auditor = "SELECT nombre, documento, apellido, cargo, firma_auditor_pdf FROM usuarios_app WHERE Id = ?";

        if (count($result_encontrar_auditor_cambio) !== 0 && $result_encontrar_auditor_cambio[0]->id_autor_cambio !== null) {
            $result_firma_auditor = DB::connection()->select(DB::raw($query_firma_auditor), [
                $result_encontrar_auditor_cambio[0]->id_autor_cambio
            ]);

            if (count($result_firma_auditor) !== 0 && $result_firma_auditor[0]->firma_auditor_pdf !== null) {
                $nombre_apellido_auditor = $result_firma_auditor[0]->nombre . " " . $result_firma_auditor[0]->apellido;
                $cedula_auditor = $result_firma_auditor[0]->documento;
                $cargo_auditor = strtoupper($result_firma_auditor[0]->cargo);
                $ruta_firma_auditor = $result_firma_auditor[0]->firma_auditor_pdf;
    
                if (Storage::disk('local')->exists($ruta_firma_auditor)) {
                    $archivo_firma_auditor = Storage::get($ruta_firma_auditor);
    
                    $base64_firma_auditor = base64_encode($archivo_firma_auditor);
                }
            }
        }

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

            if (str_contains($config->value, 'Pagina')) {
                $config_pagina = $config->value;
            }
        }

        $configs_json = json_encode([
            "codigo" => $config_codigo,
            "version" => $config_version,
            "estandar" => $config_estandar,
            "pagina" => $config_pagina
        ]);

        $query_placa = "SELECT placa FROM equipos WHERE ID_Equipo = ?";

        $result_placa = DB::connection()->select(DB::raw($query_placa), [
            $request->id_movil
        ]);

        $placa = json_encode($result_placa);

        $fecha_formato = date('Y-m-d');

        $descripcion_solucion = $request->nota_auditoria;

        $pdf = Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadView('formato_exportacion_auditorias', [
            'configs_json' => $configs_json,
            'placa' => $placa,
            'fecha_formato' => $fecha_formato,
            'descripcion_solucion' => $descripcion_solucion,
            'archivo_base64' => $archivo_base64,
            'base64_firma' => $base64_firma !== ""? $base64_firma : "",
            'base64_firma_auditor' => $base64_firma_auditor !== ""? $base64_firma_auditor : "",
            'nombre_apellido_solucion' => $nombre_apellido_solucion,
            'cargo_solucion' => $cargo_solucion,
            'cedula_solucion' => $cedula_solucion,
            'nombre_apellido_auditor' => $nombre_apellido_auditor,
            'cargo_auditor' => $cargo_auditor,
            'cedula_auditor' => $cedula_auditor
        ]);

        return $pdf->download('prueba.pdf');
    }

    public function verNotaUltimaRevision(Request $request) {
        $query_ultima_nota = "SELECT id_cambio, comentarios, revision_despues, imagen_adjunta, fecha_registro FROM entrega_turnos_novedades_bitacora_cambios WHERE id_novedad = ? 
        ORDER BY id_cambio DESC LIMIT 1";

        $result_ultima_nota = DB::connection()->select(DB::raw($query_ultima_nota), [
            $request->id_novedad
        ]);

        if ($result_ultima_nota !== []) {
            if ($result_ultima_nota[0]->comentarios == null) {
                $result_ultima_nota[0]->comentarios = "";
            }
    
            if ($result_ultima_nota[0]->revision_despues == null) {
                $result_ultima_nota[0]->revision_despues = "";
            }
        }

        return $result_ultima_nota;
    }

    public function verNotaUltimaAudtoria(Request $request) {
        $query_ultima_auditoria = "SELECT comentarios_auditoria FROM entrega_turnos_bitacora_cambios_auditoria WHERE 
        id_novedad = ? ORDER BY id_cambio_auditoria DESC LIMIT 1";

        $result_ultima_auditoria = DB::connection()->select(DB::raw($query_ultima_auditoria), [
            $request->id_novedad
        ]);

        if ($result_ultima_auditoria !== []) {

            if ($result_ultima_auditoria[0]->comentarios_auditoria == null) {
                $result_ultima_auditoria[0]->comentarios_auditoria = "";
            }

        }

        return $result_ultima_auditoria;
    }
}
