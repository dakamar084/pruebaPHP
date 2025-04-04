<?php

require 'conexion.php';

session_start();

$correo = $_SESSION["correo"];

$stm = $cone -> prepare("SELECT c.id_campeonato,c.tallaMinima, c.nombre, c.fechaInicio
FROM campeonatos c
WHERE c.id_campeonato IN (
    SELECT DISTINCT j.id_campeonato
    FROM jornadas j
    JOIN mangas m ON j.id_jornada = m.id_jornada
    JOIN turnos t ON m.id_manga = t.id_manga
    JOIN participantes_turnos pt ON t.id_turno = pt.id_turno
    JOIN participantes p ON pt.id_participante = p.id_participante
    WHERE p.correo = ?
);");
$stm -> bind_param("s", $correo);

$stm -> execute();

$result = $stm -> get_result();

while($row = $result -> fetch_assoc()){
    echo '<p style="display:none;">'.$row["id_campeonato"].'<div onclick="clickGeneral(this)" class="campeonatoHistorial"><p class="campeonato">'.$row["nombre"]."\t|\t".$row["fechaInicio"].'</p><p onclick="event.stopPropagation();rotar(this,'.$row["id_campeonato"].', '.$row["tallaMinima"].')" id="girar" class="girar">&gt</p><div onclick="event.stopPropagation()" class="datos" style="display:none;"><hr></div></div>';
}


