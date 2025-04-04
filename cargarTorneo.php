<?php

header("Content-Type:application/json");

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $torneo = $_GET["torneo"];

    $stm = $cone -> prepare("SELECT count(j.id_jornada) as numJornadas , c.* FROM campeonatos c, jornadas j where j.id_campeonato = c.id_campeonato and c.id_campeonato = ?");
    $stm -> bind_param("i", $torneo);

    $stm -> execute();

    $res = $stm -> get_result();

    $ret = [];

    while( $row = $res -> fetch_assoc() ){
        $ret[] = $row;
    }
    echo json_encode($ret);
}