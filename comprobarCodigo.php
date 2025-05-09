<?php

if($_SERVER['REQUEST_METHOD'] == "POST") {
    session_start();
    $codigo = $_POST["codigo"] ?? "";
    if ($codigo == $_SESSION["codigo"]) {
        echo json_encode(["exito" => true, "mensaje" => "Codigo correcto"]);
    } else {
        echo json_encode(["exito" => false, "mensaje" => "Codigo incorrecto"]);
    }
} else {
    echo json_encode(["exito" => false, "mensaje" => "Metodo no permitido"]);
}