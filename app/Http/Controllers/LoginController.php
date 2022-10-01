<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function authenticate (Request $request) {

        // Variable la cual valida los valores del objeto y ver si hay coincidencias
        $fields = $request->validate([
            'documento' => 'string',
            'clave' => 'string'
        ]);

        
        // Consigue el cargo de la persona que hizo login mediante una query
        $query_cargo = "SELECT cargo FROM usuarios_app WHERE documento = ? LIMIT 1";

        $resultado_cargo = DB::connection()->select(DB::raw($query_cargo), [$request->documento]);

        // Si el cargo detectado en la query es un auxiliar...
         if ($resultado_cargo[0]->cargo == 'Auxiliar') {
            
             // Consigue la información de ese usuario
             $query_autenticacion_auxiliar = "SELECT Id, nombre, apellido, documento, password FROM usuarios_app
             WHERE documento = ? LIMIT 1";

             $result_autenticacion_auxiliar = DB::connection()->select(DB::raw($query_autenticacion_auxiliar), [$request->documento]);

             //Y si el resultado tiene algún registro y además las claves coinciden
             if ($request->documento == $result_autenticacion_auxiliar[0]->documento && $request->clave 
             == $result_autenticacion_auxiliar[0]->password) {

                 //Devuelve una respuesta con la información solicitada antes en la query de autenticación,
                 //además de otorgar un token para poder acceder a rutas protegidas (en proceso)
                 return response([
                     'datos_usuario' => $result_autenticacion_auxiliar,
                     'token' => 'placeholder'
                 ], 201);
             }

             // Si no existe alguna de las dos equivalencias, suelta un error.
             else {
                 return response([
                     'mensaje_error' => 'Datos incorrectos, por favor intenta de nuevo'
                 ], 401);
             }
         }

         /* En cambio, si el cargo que detecta es un conductor */
         else if ($resultado_cargo[0]->cargo == 'Conductor') {

             /* Hace una query la cual busca la info del usuario a base del documento */
             $query_autenticacion_conductor = "SELECT Id, nombre, apellido, documento FROM usuarios_app
             WHERE documento = ? LIMIT 1";

             $result_autenticacion_conductor = DB::connection()->select(DB::raw($query_autenticacion_conductor), [$request->documento]);

             /* Si existe algún resultado y además las claves coinciden */
             if ($request->documento == $result_autenticacion_conductor[0]->documento && $request->clave 
             == $result_autenticacion_conductor[0]->password) {

                 //Devuelve una respuesta con la información solicitada antes en la query de autenticación
                 return response([
                     'datos_usuario' => $result_autenticacion_conductor,
                     'token' => 'placeholder'
                 ], 201);
             }

             /* De lo contrario, lanza un error */
             else {
                 return response([
                     'mensaje_error' => 'Datos incorrectos, por favor intenta de nuevo'
                 ], 401);
             }
         }

         /* Si el cargo no es ni auxiliar ni conductor, lanza error */
         else {
             return response([
                 'mensaje_no_encontrado' => 'No se ha encontrado ningún auxiliar o conductor con este documento. Intenta de nuevo'
             ]);
         }
    }
}
