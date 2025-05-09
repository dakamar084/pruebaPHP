<?php

header("Content-Type: application/json");

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){

    $equipo = $_GET["correo"];
    $cant = $_GET["tipo"] == "duos" ? 2 : 4;
    $stm = $cone -> prepare("SELECT * from jugadores_equipo where id_equipo in(SELECT id_equipo from equipo where nombre like ?)");
    $stm -> bind_param("s", $equipo);
    $stm -> execute();
    echo $stm -> num_rows == 2 ? json_encode(["valido"=>true, "mensaje"=>""]) : json_encode(["valido"=> false, "mensaje"=>"ese grupo no tiene los suficientes miembros todavia"]);
}