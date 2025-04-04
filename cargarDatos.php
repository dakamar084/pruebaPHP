<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $camp = $_GET["campeonato"];
    $talla = $_GET["talla"];
    session_start();
    $correo = $_SESSION["correo"];

    $stm_piezas = $cone -> prepare("SELECT longitud from piezas where id_participante in
(SELECT id_participante from participantes_turnos where id_participante in
(SELECT id_participante from participantes where correo like ?)) AND

id_turno in(SELECT id_turno from participantes_turnos where id_turno in
       		(SELECT id_turno from turnos where id_manga in
             (SELECT id_manga from mangas where id_jornada IN
             (SELECT id_jornada from jornadas where id_campeonato = ?)))) order by longitud;");

$stm_piezas -> bind_param("ss", $correo, $camp);

$stm_piezas -> execute(); 
$result = $stm_piezas -> get_result();

echo '<hr><p class="piezasP">tus piezas: </p><ul>';
while($row = $result -> fetch_assoc()){
    $color = $row["longitud"] >= $talla ? "green" : "red";
    echo '<li style="color:'.$color.';">'.$row["longitud"]." cm</li>";
}
echo '</ul></p><p style="font-style:italic;">* las piezas en rojo no superaron la talla minima para ese campeonato, las verdes si lo hicieron</p>';

$stm_ranking = $cone -> prepare("SELECT p.correo, SUM(
	CASE 
    	WHEN longitud < 15 then 15
    	ELSE 100 + (20*longitud)
    END) as suma from piezas pt, participantes p
    
    where
    p.id_participante = pt.id_participante and id_turno in(
    SELECT id_turno from piezas where id_participante in
(SELECT id_participante from participantes_turnos where id_participante in
(SELECT id_participante from participantes)) AND

id_turno in(SELECT id_turno from participantes_turnos where id_turno in
       		(SELECT id_turno from turnos where id_manga in
             (SELECT id_manga from mangas where id_jornada IN
             (SELECT id_jornada from jornadas where id_campeonato = ?))))
    ) GROUP by pt.id_participante order by suma desc;");

$stm_ranking -> bind_param("i", $camp);

$stm_ranking -> execute();

$result = $stm_ranking -> get_result();

echo '<p>ranking:</p><ol>';

while($row = $result -> fetch_assoc()){
    $estilo = $correo == $row["correo"] ? "bold" : "normal";
    echo '<li style="font-weight:'.$estilo.'">'.$row["correo"].'→'.$row["suma"].'</li>';
}
echo "</ol>";

}