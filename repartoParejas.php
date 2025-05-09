<?php

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $stm = $cone->prepare("SELECT id_manga from mangas m 
                                INNER JOIN jornadas j on j.id_jornada = m.id_jornada where j.id_campeonato = ?;");
    $stm->bind_param("i", $_GET["campeonato"]);

    $stm->execute();
    $resp = $stm->get_result();
    $mangas = [];
    while($row = $resp -> fetch_assoc()){
        $mangas[] = $row["id_manga"];
    }
    foreach ($mangas as $manga) {
        $stm_manga = $cone->prepare("SELECT pt.id_participante, pt.cargo FROM participantes_turnos pt
                                            INNER JOIN turnos t on t.id_turno = pt.id_turno where id_manga = ? and t.numero_turno = 1;");
        $stm_manga -> bind_param("i", $manga);
        $parejas = [];
        $stm_manga -> execute();
        $todos = [];
        $result = $stm_manga -> get_result();
        while($row = $result -> fetch_assoc()){
            $todos[] = $row;
        }

        // Clasificamos los participantes en dos grupos: pescadores y controles
        $pescadores = [];
        $controles = [];
        
        foreach ($todos as $participante) {
            if ($participante['cargo'] == 'pescador') {
                $pescadores[] = $participante;
            } else if ($participante['cargo'] == 'control') {
                $controles[] = $participante;
            }
        }

        // Aseguramos que ambos grupos tengan el mismo número de participantes
        if (count($pescadores) != count($controles)) {
            echo "El número de pescadores y controles no es el mismo.";
            continue; // Saltamos esta manga si los grupos no tienen el mismo número de participantes
        }

        // Mezclamos aleatoriamente los grupos
        shuffle($pescadores);
        shuffle($controles);

        // Emparejamos pescadores con controles
        for ($i = 0; $i < count($pescadores); $i++) {
            $parejas[] = $pescadores[$i]["id_participante"] . '-' . $controles[$i]["id_participante"];
            $parejas[] = $controles[$i]["id_participante"] . '-' . $pescadores[$i]["id_participante"];
        }

        foreach($parejas as $pareja)
        {
                $parejaSeparada = explode("-", $pareja);
                $stm_update = $cone -> prepare("UPDATE participantes_turnos pt set pareja = ? where id_turno in(SELECT id_turno from turnos where id_manga = ?) and pt.id_participante = ?;");
                $stm_update -> bind_param("sis", $parejaSeparada[1], $manga, $parejaSeparada[0]);
                $stm_update -> execute();
        }
    }
    echo "parejas creadas correctamente";
}
