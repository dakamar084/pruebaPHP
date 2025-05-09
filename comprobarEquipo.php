<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $nombre = $_GET["equipo"];

    $stm = $cone -> prepare("SELECT * from equipo where nombre like ?");
    $stm -> bind_param("s", $nombre);
    $stm -> execute();
    $stm -> store_result();
    echo $stm -> num_rows >= 1 ? "true" : "false";
}