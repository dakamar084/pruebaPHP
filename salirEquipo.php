<?php

require 'conexion.php';

session_start();

$correo = isset($_GET["correo"]) ? $_GET["correo"] : $_SESSION["correo"];

$esAdmin = $cone -> prepare("SELECT rol from jugadores_equipo where id_participante in(SELECT id_participante from participantes where correo like ?) and estado like 'aceptado'");
$esAdmin -> bind_param("s", $correo);
$esAdmin -> execute();

$esAdmin -> bind_result($rol);
$esAdmin -> fetch();

$esAdmin -> close();

if($rol == "admin"){
    $nuevoAdmin = $cone -> prepare("SELECT correo from participantes where id_participante in
                                            (SELECT id_participante from jugadores_equipo where id_equipo in
                                            (SELECT id_equipo from jugadores_equipo where id_participante in(SELECT id_participante from participantes where correo like ?) and estado like 'aceptado')) and correo not like ? order by rand() limit 1;");

    $nuevoAdmin -> bind_param("ss", $correo, $correo);
    $nuevoAdmin -> execute();

    $nuevoAdmin -> bind_result($correoAdmin);
    $nuevoAdmin -> fetch();

    $nuevoAdmin -> close();
    $stm_admin = $cone -> prepare("UPDATE jugadores_equipo set rol = 'admin' where id_participante in(SELECT id_participante from participantes where correo like ?)");
    $stm_admin -> bind_param("s", $correoAdmin);
    $stm_admin -> execute();

    $stm_admin -> execute();
    echo $correoAdmin;
}


$stm = $cone -> prepare("DELETE FROM jugadores_equipo where id_participante in(SELECT id_participante from participantes where correo like ?) and estado like 'aceptado'");

$stm -> bind_param("s", $correo);
$stm -> execute();
$stm -> close();