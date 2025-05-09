<?php

header("Content-Type: application/json");

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $body = json_decode(file_get_contents("php://input"), true);
    $correo = $body["correo"];
    $contra = $body["contra"];
    // Preparamos la consulta
    $stm = $cone->prepare("SELECT verificado, contraseña, rol FROM participantes WHERE correo = ?");
    $stm->bind_param("s", $correo);
    $stm->execute();
    $result = $stm->get_result(); // Obtenemos el resultado de la consulta          \\  
                                 //                                                  \\ 
                                // Verificamos si la consulta devolvió algún resultado\\
    if ($result->num_rows > 0) {
        // Obtenemos la contraseña hasheada del resultado
        $row = $result->fetch_assoc();
        $contraHas = $row['contraseña'];  // Contraseña hasheada de la base de datos
        if($row["verificado"] == 0){
            die( json_encode(["existe"=>false, "mensaje" =>"todavia no has verificado tu usuario, comprueba tu bandeja de correo"]));
        }
        // Comprobamos si la contraseña introducida coincide con la almacenada
        if (password_verify($contra, $contraHas)) {
            session_start();
            $_SESSION["correo"] = $correo;
            $fecha = date("d-m-Y h:m");
            echo json_encode(["existe"=>true, "mensaje"=>$fecha, "vista"=>$row["rol"].".php"]);
        } else {
            echo json_encode(["existe"=>false, "mensaje"=>"Datos de acceso incorrectos"]);
        }
    } else {
        echo  json_encode(["existe"=>false, "mensaje"=>'Ese correo no pertenece a ningun usuario']);
    }

    // Cerramos el statement
    $stm->close();
}
