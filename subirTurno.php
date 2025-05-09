<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $campeonato = (int)$_GET["campeonato"];
    $stm = $cone -> prepare("UPDATE campeonatos set turnoActual = turnoActual + 1 where id_campeonato = ?");
    $stm -> bind_param("i", $campeonato);
    $stm -> execute();
    echo $stm -> affected_rows >=1 ? "turno actualizado correctamente" : "hubo un error al actualizar el turno";    
}