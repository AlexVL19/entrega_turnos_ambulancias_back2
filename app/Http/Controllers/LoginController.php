<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Usuarios;

class LoginController extends Controller
{
    public function authenticate (Request $request) {

        // Variable la cual valida los valores del objeto y ver si hay coincidencias
        $fields = $request->validate([
            'documento' => 'required|string',
            'clave' => 'required|string'
        ]);

        
        // Consigue el cargo de la persona que hizo login mediante una query
        $query_cargo = "SELECT cargo FROM usuarios_app WHERE documento = ? LIMIT 1";

        $resultado_cargo = DB::connection()->select(DB::raw($query_cargo), [$fields["documento"]]);

        // Si el cargo detectado en la query es un auxiliar...
         if (count($resultado_cargo) > 0 && $resultado_cargo[0]->cargo == 'Auxiliar') {
            
            // Consigue la información de ese usuario
            $autenticacion_auxiliar = Usuarios::where('documento', $fields["documento"])->first();

              //Y si los documentos y las claves coinciden
              if ($fields["documento"] == $autenticacion_auxiliar->documento && $fields["clave"] 
              == $autenticacion_auxiliar->password) {

                //Crea un token que será validado al entrar en cada ruta
                $token = $autenticacion_auxiliar->createToken('apitoken')->plainTextToken;

                //Devuelve una respuesta con la información solicitada antes en la query de autenticación,
                //además de otorgar un token para poder acceder a rutas protegidas (en proceso)
                return response([
                    'datos_usuario' => $autenticacion_auxiliar,
                    'token' => $token
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
         else if (count($resultado_cargo) > 0 && $resultado_cargo[0]->cargo == 'Conductor') {

             /* Hace una query la cual busca la info del usuario a base del documento */
             $autenticacion_conductor = Usuarios::where('documento', $fields["documento"])->first();

             /* Si los documentos y las claves coinciden */
             if ($fields["documento"] == $autenticacion_conductor->documento && $fields["clave"]
             == $autenticacion_conductor->password) {

                //Crea un token que será validado al entrar en cada ruta
                $token = $autenticacion_auxiliar->createToken('apitoken')->plainTextToken;

                 //Devuelve una respuesta con la información solicitada antes en la query de autenticación
                 return response([
                     'datos_usuario' => $autenticacion_conductor,
                     'token' => $token
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
             ], 401);
         }
    }
}
