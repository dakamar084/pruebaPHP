<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"),true);
    $contra = password_hash($body["contraseña"],PASSWORD_BCRYPT);
    $stm = $cone -> prepare("INSERT into participantes(nombre,correo, contraseña, rol) values(?,?,?,'supervisor')");
    $stm -> bind_param("sss", $body["nombre"],$body["correo"],$contra);
    $stm -> execute();
    echo $stm -> affected_rows == 1 ? "supervisor registrado correctamente" : "hubo un problema al registrar al supervisor";
}