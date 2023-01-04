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
            <td class="tg-0lax">
                <img src="data:image/png;base64,{{ $archivo_base64 }}" style="height: 100px; width: 100px; padding: 10px; margin-left: 10px">
            </td>
            <td class="tg-0lax" colspan="2" style="text-align: center; align-content: center">
                FORMULARIO LLENADO
            </td>
            <td class="tg-0lax" style="text-align: center; align-content: center">
                <div>FECHA</div>
                {{ $fecha_formato }}    
            </td>
          </tr>
        </thead>
    </table>

    <div>
        @for ($index = 0; $index < count(json_decode($formulario_vistas, true)); $index++)
            {{ json_encode(json_decode($formulario_vistas)[$index]) }}
        @endfor
    </div>
</body>