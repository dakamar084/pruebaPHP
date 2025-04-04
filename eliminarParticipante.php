<?php

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $body = json_decode(file_get_contents("php://input"),true);
    $stm = $cone -> prepare("DELETE FROM participantes_turnos where id_participante in
                                    (SELECT id_participante from participantes where correo like ?) and
                                    id_turno in
                                    (SELECT id_turno from turnos where id_manga in 
                                    (SELECT id_manga from mangas where id_jornada in
                                    (SELECT id_jornada from jornadas where id_campeonato = ?)))");
    $stm -> bind_param("si", $body["correo"], $body["campeonato"]);
    $stm -> execute();
    
    echo $stm -> affected_rows >= 1 ? "participante eliminado correctamente" : "hubo un problema al eliminar el participante";
}