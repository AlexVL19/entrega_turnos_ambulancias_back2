<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Formato de solución de novedad</title>
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
            <td class="tg-0lax" rowspan="2">
                <img src="data:image/png;base64,{{ $archivo_base64 }}" style="height: 100px; width: 100px; padding: 30px;">
            </td>
            <td class="tg-0lax" colspan="2" style="text-align: center">
                FORMATO DE SOLUCION DE AUDITORÍA
            </td>
            <td class="tg-0lax" style="text-align: center">
                <div>CÓDIGO</div>
                {{ json_decode($configs_json)->codigo }}    
            </td>
          </tr>
          <tr>
            <td class="tg-0lax" style="text-align: center">
                <div>VERSION</div>
                {{ json_decode($configs_json)->version }}    
            </td>
            <td class="tg-0lax" style="text-align: center">
                <div>ESTANDAR</div>
                {{ json_decode($configs_json)->estandar }}    
            </td>
            <td class="tg-0lax" style="text-align: center">
                <div>PAGINA</div>
                {{ json_decode($configs_json)->pagina }}    
            </td>
          </tr>
        </thead>
    </table>

    <div style="font-family: Arial, sans-serif; font-size:14px; display: flex; margin-top: 50px">
        <div style="justify-content: start; margin-bottom: 20px">
            <b>FECHA: </b> {{ $fecha_formato }}
        </div>

        <div style="justify-content: start; margin-bottom: 20px">
            <b>PLACA DEL VEHICULO: </b> {{ json_decode($placa, true)[0]['placa'] }}
        </div>

        <div style="justify-content: start; margin-bottom: 30px">
            <b>FIRMA DEL LIDER DE MANTENIMIENTO: </b>
        </div>

        <div style="justify-content: center; text-align: center;">
            <b>DESCRIPCION DE LA AUDITORIA
                <div style="margin-top: 10px; text-align: justify; border: 1px black solid; height: 200px; padding: 10px">
                    {{ $descripcion_solucion }}
                </div>
            </b>
        </div>

        <div style="justify-content: start; margin-top: 30px">
            <b>FIRMA DEL COORDINADOR DE CALIDAD: </b>
        </div>  
    </div>
</body>