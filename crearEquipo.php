<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"), true);
    session_start();
    $correo = $_SESSION["correo"];

    $stm = $cone -> prepare("INSERT into equipo(nombre, tipoEquipo) values (?,?)");
    $stm -> bind_param("ss", $body["nombre"], $body["tipo"]);
    $stm -> execute();
    $id_equipo = $cone -> insert_id;

    $stm_añadir = $cone -> prepare("INSERT into jugadores_equipo(id_equipo, id_participante, rol) values (?, (SELECT id_participante from participantes where correo like ?), 'admin')");
    $stm_añadir -> bind_param("is", $id_equipo, $correo);
    $stm_añadir -> execute();
    echo "equipo creado correctamente";
}