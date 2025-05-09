<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"), true);

    $stm = $cone -> prepare("INSERT into jugadores_equipo(id_equipo, id_participante, estado) values((SELECT id_equipo from equipo where nombre like ?),(SELECT id_participante from participantes where correo like ?), 'pendiente')");
    $stm -> bind_param("ss", $body["equipo"], $body["usuario"]);
    $stm -> execute();
    echo $stm -> affected_rows >= 1 ? "jugador invitado correctamente" : "error al invitar al jugador";
}