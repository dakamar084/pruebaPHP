<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $campeonato = $_GET["campeonato"] ?? null;
    if (!$campeonato) {
        echo "Error: Campeonato no especificado";
        exit;
    }
    $tipo = $_GET["tipo"];
    if($tipo == "individual"){
        $stm = $cone->prepare("SELECT correo FROM participantes WHERE id_participante IN 
                                (SELECT id_participante FROM participantes_turnos WHERE id_turno IN 
                                (SELECT id_turno FROM turnos WHERE id_manga IN 
                                (SELECT id_manga FROM mangas WHERE id_jornada IN 
                                (SELECT id_jornada FROM jornadas WHERE id_campeonato = ?))))");
        $stm->bind_param("i", $campeonato);
        $stm->execute();
        $res = $stm->get_result();
        while ($row = $res->fetch_assoc()) {
            echo '<div class="participante">
                    <p data-correo="' . htmlspecialchars($row["correo"]) . '">' . htmlspecialchars($row["correo"]) . '</p>
                    <img src="cerrar.png" data-correo="' . htmlspecialchars($row["correo"]) . '" data-campeonato="' . htmlspecialchars($campeonato) . '"/>
                    </div><hr>';
        }
        echo '</div>
        </div>';
    }
    else{
        $stm = $cone -> prepare("SELECT distinct nombre from equipo e 
                                        INNER JOIN jugadores_equipo je on je.id_equipo = e.id_equipo
                                        INNER JOIN participantes_turnos pt on pt.id_participante = je.id_participante
                                        INNER JOIN turnos t on t.id_turno = pt.id_turno
                                        INNER JOIN mangas m on m.id_manga = t.id_manga
                                        INNER JOIN jornadas j on j.id_jornada = m.id_jornada
                                        WHERE j.id_campeonato = ?");
        $stm -> bind_param("i", $campeonato);
        $stm -> execute();
        $res = $stm -> get_result();
        while ($row = $res->fetch_assoc()) {
            echo '<div class="participante">
                    <p data-nombre="' . htmlspecialchars($row["nombre"]) . '">' . htmlspecialchars($row["nombre"]) . '</p>
                    <img src="cerrar.png" data-correo="' . htmlspecialchars($row["nombre"]) . '" data-campeonato="' . htmlspecialchars($campeonato) . '"/>
                    </div><hr>';
        }
        echo '</div>
        </div>';
    }
}