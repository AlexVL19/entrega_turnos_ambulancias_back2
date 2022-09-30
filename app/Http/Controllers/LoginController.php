<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function authenticate (Request $request) {
        $fields = $request->validate([
            'documento' => 'required|string',
            'clave' => 'required|string'
        ]);

        
        // Consigue el cargo de la persona que hizo login mediante una query
        $query_cargo = "SELECT cargo FROM usuarios_app WHERE documento = ? LIMIT 1";

        $resultado_cargo = DB::connection()->select(DB::raw($query_cargo), [$fields["documento"]]);

        // Si el cargo detectado en la query es un auxiliar...
        if ($resultado_cargo[0]->cargo == 'Auxiliar') {
            
            // Consigue la información de ese usuario
            $query_autenticacion_auxiliar = "SELECT Id, nombre, documento FROM usuarios_app
            WHERE documento = ? LIMIT 1";

            $result_autenticacion_auxiliar = DB::connection()->select(DB::raw($query_autenticacion_auxiliar));

            //Y si el resultado no está vacío y además las claves coinciden
            if (count($result_autenticacion_auxiliar) != 0 && $fields["clave"] 
            == $result_autenticacion_auxiliar[0]->password) {

                //Devuelve una respuesta con la información solicitada antes en la query de autenticación
                return response([
                    'datos_usuario' => $result_autenticacion_auxiliar,
                    'token' => 'placeholder'
                ], 201);
            }

            else {
                return response([
                    'mensaje_error' => 'Datos incorrectos, por favor intenta de nuevo'
                ], 401);
            }
        }

        else if ($resultado_cargo[0]->cargo == 'Conductor') {

            $query_autenticacion_conductor = "SELECT Id, nombre, documento FROM usuarios_app
            WHERE documento = ? LIMIT 1";

            $result_autenticacion_conductor = DB::connection()->select(DB::raw($query_autenticacion_conductor));

            if (count($result_autenticacion_conductor) != 0 && $fields["clave"] 
            == $result_autenticacion_conductor[0]->password) {

                //Devuelve una respuesta con la información solicitada antes en la query de autenticación
                return response([
                    'datos_usuario' => $result_autenticacion_conductor,
                    'token' => 'placeholder'
                ], 201);
            }

            else {
                return response([
                    'mensaje_error' => 'Datos incorrectos, por favor intenta de nuevo'
                ], 401);
            }
        }

        else {
            return response([
                'mensaje_no_encontrado' => 'No se ha encontrado ningún auxiliar o conductor con este documento. Intenta de nuevo'
            ]);
        }
    }
}
