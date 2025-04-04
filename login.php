<?php

header("Content-Type: application/json");

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $body = json_decode(file_get_contents("php://input"), true);
    $correo = $body["correo"];
    $contra = $body["contra"];
    // Preparamos la consulta
    $stm = $cone->prepare("SELECT contraseña,rol FROM participantes WHERE correo = ?");
    $stm->bind_param("s", $correo);
    $stm->execute();
    $result = $stm->get_result(); // Obtenemos el resultado

    // Verificamos si la consulta devolvió algún resultado
    if ($result->num_rows > 0) {
        // Obtenemos la contraseña hasheada del resultado
        $row = $result->fetch_assoc();
        $contraHas = $row['contraseña'];  // Contraseña hasheada de la base de datos

        // Comprobamos si la contraseña introducida coincide con la almacenada
        if (password_verify($contra, $contraHas)) {
            session_start();
            $_SESSION["correo"] = $correo;
            echo json_encode(["existe"=>true, "mensaje"=>"Login exitoso", "vista"=>$row["rol"].".php"]);
        } else {
            echo json_encode(["existe"=>false, "mensaje"=>"Datos de acceso incorrectos"]);
        }
    } else {
        echo 'Correo no encontrado';
    }

    // Cerramos el statement
    $stm->close();
}
