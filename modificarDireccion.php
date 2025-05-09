<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    
    session_start();

    $body = json_decode(file_get_contents("php://input"),true);
    $direccion = $body["direccion"] ?? ' ';
    $cp = $body["cp"] ?? ' ';
    $provincia = $body["provincia"] ?? ' ';
    $pais = $body["pais"] ?? ' ';
    $correo = $_SESSION["correo"];

    $stm = $cone -> prepare("UPDATE participantes set direccion = ?, cp = ?, provincia = ?, pais = ? where correo like ?");
    $stm -> bind_param("sssss", $direccion, $cp, $provincia, $pais, $correo);
    $stm -> execute();
    $stm -> get_result();

    if($stm ->affected_rows == 1){
        echo "Cambios guardados correctamente";
    }
    else{
        echo "no se realizaron cambios";
    }
    
}