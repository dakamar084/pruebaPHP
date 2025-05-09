<?php

require 'conexion.php';

session_start();

$correo = $_SESSION["correo"];

$stm = $cone -> prepare("SELECT * from jugadores_equipo where id_participante in(SELECT id_participante from participantes where correo like ?) and estado like 'pendiente'");
$stm -> bind_param("s", $correo);

$stm -> execute();

$res = $stm -> get_result();

echo $res -> num_rows;