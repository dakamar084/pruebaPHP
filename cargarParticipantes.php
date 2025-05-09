<?php

require 'conexion.php';

$stm = $cone -> prepare('SELECT * from participantes where rol not like "admin"');

$stm -> execute();

$res = $stm -> get_result();

echo'
<table>
    <th>nombre</th>
    <th>apellidos</th>
    <th>correo</th>
    <th>telefono</th>
    <th>direccion</th>
    <th>Codigo Postal</th>
    <th>provincia</th>
    <th>Fecha de nacimiento</th>
    <th>pais</th>
    <th>numero de licencia</th>
    <th>Número de federación</th>
    <th>permisos de datos de interes</th>
    <th>permisos de notificaciones</th>
    <th>rol</th>
    <th>acciones</th>
';

while( $row = $res -> fetch_assoc() ){
    
    $interes = $row["interes"] == 1 ? "si":"no";
    $notis = $row["notificaciones"] == 1 ? "si":"no";

    echo '<tr data-id="'.$row["id_participante"].'">
    <td><input type="text" name="nombre" value="'.$row["nombre"].'"/></td>
    <td><input type="text" name="apellidos" value="'.$row["apellidos"].'"/></td>
    <td><input type="text" name="correo" value="'.$row["correo"].'"/></td>
    <td><input type="text" name="telefono" value="'.$row["telefono"].'"/></td>
    <td><input type="text" name="direccion" value="'.$row["direccion"].'"/></td>
    <td><input type="text" name="CP" value="'.$row["CP"].'"/></td>
    <td><input type="text" name="provincia" value="'.$row["provincia"].'"/></td>
    <td><input type="text" name="fechaNac" value="'.$row["fechaNac"].'"/></td>
    <td><input type="text" name="pais" value="'.$row["pais"].'"/></td>
    <td><input type="text" name="numLicencia" value="'.$row["numLicencia"].'"/></td>
    <td><input type="text" name="numFede" value="'.$row["numFede"].'"/></td>
    <td><input type="text" name="interes" value="'.$interes.'"/></td>
    <td><input type="text" name="notificaciones" value="'.$notis.'"/></td>
    <td><input type="text" name="rol" value="'.$row["rol"].'"/></td>
    <td class="acciones"><img title="eliminar participante" data-action="eliminar" src="eliminar.png"><img src="editar.png" data-action="modificar" title="modificar contraseña"></img>
</tr>';
}
echo '</table>';