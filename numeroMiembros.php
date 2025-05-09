<?php


require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $stm = $cone -> prepare("SELECT * from jugadores_equipo where estado like 'aceptado' and id_equipo in(SELECT id_equipo from equipo where nombre like ?)");
    $stm -> bind_param("s",$_GET["equipo"]);
    $stm -> execute();
    $res = $stm -> get_result();

    echo $res -> num_rows;
}
