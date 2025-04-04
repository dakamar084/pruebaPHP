<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD']=="POST"){
    var_dump($_POST);

    $id_participante = $_POST["id_participante"];
    $nombre = $_POST["nombre"];
    $apellidos = $_POST["apellidos"];
    $correo = $_POST["correo"];
    $telefono = $_POST["telefono"];
    $pais = $_POST["pais"];
    $provincia = $_POST["provincia"];
    $fechaNac = $_POST["fechaNac"];
    $numLicencia = $_POST["numLicencia"];
    $numFede = $_POST["numFede"];
    $interes = $_POST["interes"] == "si" ? 1:0;
    $notificaciones = $_POST["notificaciones"] == "si" ? 1:0;
    $rol = $_POST["rol"];
    $cp = $_POST["CP"];

    $stm = $cone -> prepare("UPDATE participantes set nombre = ?, apellidos = ?, correo = ?, telefono = ?, pais = ?, provincia = ?,CP=?, fechaNac = ?, numLicencia = ?, numFede = ?, interes = ?, notificaciones = ?, rol = ? where id_participante = ?");
    $stm -> bind_param("ssssssssiiiisi",$nombre, $apellidos, $correo, $telefono, $pais,$provincia,$cp, $fechaNac, $numLicencia, $numFede, $interes, $notificaciones,$rol,$id_participante);

    $stm -> execute();

    echo $stm -> num_rows >=1 ? "participante modificado correctamente" : "no se modificaron campos";
}