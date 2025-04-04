<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"),true);

$stm = $cone -> prepare("INSERT INTO campeonatos_participantes values(?,(SELECT id_participante from participantes where correo like ?))");
    $stm -> bind_param("is", $body["id_campeonato"], $body["correo"]);
    $stm -> execute();
    $stm -> get_result();

    echo $stm -> affected_rows == 1?"registro completado correctamente":"hubo un error al completar el registro";
}