<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $numLicencia = $_POST['licencia'];
    $numFede = $_POST["fede"];
    $permisos = $_POST["permisos"];
    $endpoint = $_POST["endpoint"] == "null" ? null : $_POST["endpoint"];

    $notis = $permisos[0];
    $interes = $permisos[1];

    session_start();

    $correo = $_SESSION["correo"];

    $stm = $cone -> prepare("UPDATE participantes set numLicencia = ?, numFede = ?, notificaciones = ?, interes = ?, endpoint = ? where correo like ?");
    $stm -> bind_param("ssiiss", $numLicencia,$numFede, $notis, $interes, $endpoint, $correo);

    $stm -> execute();
    $stm -> get_result();

    echo $stm -> affected_rows == 1 ? "datos modificados correctamente" : "no se modificaron datos";
}