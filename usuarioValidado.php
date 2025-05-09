<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    session_start();
    $body = json_decode(file_get_contents("php://input"), true);
    $correo = $body["correo"] ?? $_SESSION["correo"];
    $stm = $cone -> prepare("SELECT verificado from participantes where correo like ?");
    $stm -> bind_param("s", $correo);
    $stm -> execute();
    $stm -> bind_result($verificado);
    $stm -> fetch();

    echo $verificado;
}