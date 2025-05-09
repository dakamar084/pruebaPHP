<?php

require 'vendor/autoload.php'; // Cargar la biblioteca de JWT
use Firebase\JWT\JWT;

if($_SERVER['REQUEST_METHOD'] === "POST"){
    $body = json_decode(file_get_contents("php://input"), true);
    $correo = $body["correo"];
    $payload = [
        'correo'=>$correo,
        'exp'=>time()+3600
    ];

    $token = JWT::encode($payload, "zmLBUWwT", 'HS256');

    echo $token;
}
