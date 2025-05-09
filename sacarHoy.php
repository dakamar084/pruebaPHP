<?php

require 'conexion.php';

session_start();

$correo = $_SESSION["correo"];

$hoy = date("Y-m-d");

$stm = $cone -> prepare("SELECT id_campeonato, nombre, fechaInicio, fechaFin FROM campeonatos where fechaFin >= ?;");
$stm -> bind_param("s", $hoy);

$stm -> execute();

$result = $stm -> get_result();

if($result -> num_rows == 0){
    echo "no hay campeonatos a la vista";
}
while($row = $result -> fetch_assoc()){
    if(yaApuntado($row["id_campeonato"])){
        echo '<p style="display:none;">'.$row["id_campeonato"].'<div class="campeonatoHistorial2"><p class="campeonato">'.$row["nombre"]."\t|\t<span></span>".$row["fechaInicio"].'</p><div class="sub"><button class="cancelar" onclick="cancelarSuscripcion('.$row["id_campeonato"].', \''.$correo.'\')">cancelar suscripcion al campeonato</button><p>*dejaras de recibir las notificaciones de este</p></div></div>';
    }
    else{
        echo '<p style="display:none;">'.$row["id_campeonato"].'<div class="campeonatoHistorial2"><p class="campeonato">'.$row["nombre"]."\t|\t<span></span>".$row["fechaInicio"].'</p><div class="sub"><button class="apuntar" onclick="apuntarCampeonato('.$row["id_campeonato"].', \''.$correo.'\')">suscribirme al campeonato</button><p>*solo recibiras notificaciones de este</p></div></div>';
    }
}  
function yaApuntado($campeonato){
    global $cone,$correo;
    $stm = $cone -> prepare('SELECT * from campeonatos_participantes where id_campeonato = ? and id_participante in(SELECT id_participante from participantes where correo like ?)');
    $stm -> bind_param('is', $campeonato, $correo);
    $stm -> execute();
    $res = $stm -> get_result();
    return $res -> num_rows >= 1 ? true : false;
}