<?php

require 'vendor/autoload.php';
require 'conexion.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $token = $_GET["token"] ?? "";
    try{
        $datos = JWT::decode($token, new Key("zmLBUWwT", "HS256"));
        $stm = $cone -> prepare("UPDATE participantes set verificado = 1 where correo like ?");
        $stm -> bind_param("s", $datos -> correo);
        $stm -> execute();
        if ($stm -> affected_rows >= 1) {
            echo '<div style="font-family: Arial, sans-serif; color: green; font-size: 18px; text-align: center; margin-top: 20px; padding: 20px; border: 2px solid green; background-color: #e6ffe6;">
                    Correo verificado correctamente, vuelve a la aplicación y ya podrás acceder
                  </div>';
        } else {
            echo '<div style="font-family: Arial, sans-serif; color: red; font-size: 18px; text-align: center; margin-top: 20px; padding: 20px; border: 2px solid red; background-color: #ffe6e6;">
                    Hubo un error al verificar el correo, inténtelo de nuevo más tarde
                  </div>';
        }
    }
    catch(Exception $e){
        die('<div style="font-family: Arial, sans-serif; color: red; font-size: 18px; text-align: center; margin-top: 20px; padding: 20px; border: 2px solid red; background-color: #ffe6e6;">
                Token inválido o expirado
             </div>');
    }
}