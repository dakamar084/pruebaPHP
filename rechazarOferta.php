<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $equipo = $_GET["equipo"];

    $stm = $cone -> prepare("DELETE FROM jugadores_equipo where id_equipo in(SELECT id_equipo from equipo where nombre like ?) and estado like 'pendiente'");
    $stm -> bind_param("s", $equipo);
    $stm -> execute();

}