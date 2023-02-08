<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formato de formulario llenado</title>
    <style type="text/css">
        .tg  {border-collapse:collapse;border-spacing:0;}
        .tg td{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
          overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg th{border-color:black;border-style:solid;border-width:1px;font-family:Arial, sans-serif;font-size:14px;
          font-weight:normal;overflow:hidden;padding:10px 5px;word-break:normal;}
        .tg .tg-0lax{text-align:left;vertical-align:top}
    </style>
</head>

<body>
    <table class="tg" style="undefined; table-layout: fixed; width: 100%">
        <colgroup>
        <col style="width: 165px">
        <col style="width: 146px">
        <col style="width: 133px">
        <col style="width: 109px">
        </colgroup>
        <thead>
          <tr>
            <td class="tg-0lax">
                <img src="data:image/png;base64,{{ $archivo_base64 }}" style="height: 100px; width: 100px; padding: 10px; margin-left: 10px">
            </td>
            <td class="tg-0lax" colspan="2" style="text-align: center; align-content: center">
                FORMULARIO ENTREGA DE TURNO
            </td>
            <td class="tg-0lax" style="text-align: center; align-content: center">
                <div>FECHA</div>
                {{ $fecha_formato }}    
            </td>
          </tr>
        </thead>
    </table>

    <div style="font-family: Arial, sans-serif; margin-top: 20px">
        <b>INFORMACIÓN DEL TURNO:</b>

        <br>

        <div style="margin-top: 10px">Número de turno: </div>
        <div style="margin-top: 10px">Tipo de turno: </div>
        <div style="margin-top: 10px">Fecha de apertura: </div>
        <div style="margin-top: 10px">Nombre de móvil: </div>
        <div style="margin-top: 10px">Placa de móvil: </div>
        
        <br>

        <div>Médico: </div>
        <div style="margin-top: 10px">Conductor: </div>
        <div style="margin-top: 10px">Auxiliar: </div>
    </div>



    <div style="margin-top: 40px">
        <table class="tg">
            <thead>
                <tr>
                <th class="tg-0lax" style="background-color: lightblue">Tipo de verificación</th>
                <th class="tg-0lax" style="background-color: lightblue">Respuesta</th>
                <th class="tg-0lax" style="background-color: lightblue">Comentario (novedad)</th>
                <th class="tg-0lax" style="background-color: lightblue">Valor (opcional)</th>
                <th class="tg-0lax" style="background-color: lightblue">Carga en % (opcional)</th>
                </tr>
            </thead>

            <tbody>
                @for ($index = 0; $index < count(json_decode($formulario_vistas, true)); $index++)
                    @foreach (json_decode($formulario_vistas)[$index] as $form)
                        <tr>
                        <td class="tg-0lax">{{ $form->tipo_verificacion }}</td>
                        <td class="tg-0lax">{{ $form->respuesta }}</td>
                        <td class="tg-0lax">{{ $form->comentarios }}</td>
                        <td class="tg-0lax">{{ $form->valor }}</td>
                        <td class="tg-0lax">{{ $form->carga }}</td>
                        </tr>    
                    @endforeach
                @endfor
            </tbody>
        </table>
    </div>
</body>