<?php
require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"),true);

    $stm = $cone -> prepare("DELETE from campeonatos_participantes where id_campeonato = ? and id_participante in(SELECT id_participante from participantes where correo like ?)");
    $stm -> bind_param("is", $body["id_campeonato"], $body["correo"]);
    $stm -> execute();
    $stm -> get_result();
    echo $stm -> affected_rows == 1 ? "suscripcion cancelada correctamente" : "hubo un error al cancelar la suscripcion";
}