<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $equipo = $_GET["equipo"];

    session_start();

    $correo  = $_SESSION["correo"];

    $stm = $cone -> prepare("UPDATE jugadores_equipo set estado = 'aceptado' where id_participante in(SELECT id_participante from participantes where correo like ?) and id_equipo in (SELECT id_equipo from equipo where nombre like ?)");
    $stm -> bind_param("ss", $correo, $equipo);
    $stm -> execute();

    echo $stm -> affected_rows >= 1 ? "oferta aceptada correctamente" : "hubo un error al aceptar la oferta";
}