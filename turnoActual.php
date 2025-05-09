<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $campeonato = $_GET["campeonato"];
    $stm = $cone -> prepare("SELECT turnoActual from campeonatos where id_campeonato = ?");
    $stm -> bind_param("i", $campeonato);
    $stm -> execute();
    $stm -> bind_result($turno);
    $stm -> fetch();
    
    echo $turno;
}