<?php

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $campeonato = $_GET["campeonato"] ?? '';

    $stm = $cone->prepare("SELECT longitud as piezas from piezas where id_participante in
                                  (SELECT id_participante from participantes_turnos where id_participante in
                                  (SELECT id_participante from participantes where correo like ?)) AND
                                  
                                  id_turno in(SELECT id_turno from participantes_turnos where id_turno in
                                              (SELECT id_turno from turnos where id_manga in
                                                  (SELECT id_manga from mangas where id_jornada IN
                                                  (SELECT id_jornada from jornadas where id_campeonato = ?)))) order by longitud DESC;");
    $stm->bind_param("ss", $correo, $campeonato);
    $stm->execute();
    $result = $stm->get_result();
    echo $result->num_rows;
}