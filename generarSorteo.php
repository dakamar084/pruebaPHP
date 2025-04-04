<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $camp = $_GET["campeonato"];

    $stm = $cone ->prepare("CALL AsignarCargosPorCampeonatoParejas(?)");
    $stm -> bind_param("i", $camp);
    $stm -> execute();

    echo "reparto realizado correctamente";
}