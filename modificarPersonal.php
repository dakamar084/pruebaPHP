<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"),true);
    $nombre = explode(" ",$body["nombre"])[0];
    $apellidos = explode(" ",$body["nombre"])[1].' '. explode(' ',$body['nombre'])[2];
    $correo = $body["correo"];
    $telefono = $body["telefono"];
    $fecha = $body["fechaNac"];
    $stm = $cone -> prepare("UPDATE participantes set nombre = ?, apellidos = ?, telefono = ?, fechaNac = ? where correo like ?");
    $stm -> bind_param("sssss", $nombre, $apellidos, $telefono, $fecha, $correo);
    $stm -> execute();
    $stm -> get_result();

    if($stm -> affected_rows == 1){
        echo 'Cambios actualizados correctamente';
    }
    else{
        echo "No se realizaron cambios";
    }
}