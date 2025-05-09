<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"), true);
    $correo = $body["correo"];
    $contra = password_hash($body["nuevaContra"], PASSWORD_BCRYPT);
    $stm = $cone -> prepare("UPDATE participantes set contraseña = ? where correo like ?");
    $stm -> bind_param("ss", $contra, $correo);
    $stm -> execute();
    echo $stm -> affected_rows >= 1 ? "contraseña modificada correctamente" : "hubo un error al modificar la contraseña";
}
