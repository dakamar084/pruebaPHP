<?php

require 'conexion.php';

session_start();

$correo = $_SESSION["correo"];

$stm = $cone->prepare("SELECT c.id_campeonato,c.tallaMinima,c.turnoActual, c.nombre, c.fechaInicio, c.fechaFin
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
$stm->bind_param("s", $correo);

$stm->execute();

$result = $stm->get_result();

while ($row = $result->fetch_assoc()) {
    $hoy = date("Y-m-d");
    $estado = $row["fechaInicio"] > $hoy || $row["fechaFin"] < $hoy ? '<div class="inactivo"> <p class="circulo"></p> <p>inactivo</p></div>' : '<div class="activo"><p class="circulo"></p><p>activo</p></div>';
    echo '<p style="display:none;">' . $row["id_campeonato"] . '</p> <div onclick="clickGeneral(this)" class="campeonatoHistorial">
                <div class="nombreCamp">
                    <p class="campeonato">' . $row["nombre"] . " |</p><p>\t" . $row["fechaInicio"] . $estado . '</p>
                </div>
                <p onclick="event.stopPropagation();rotar(this,' . $row["id_campeonato"] . ', ' . $row["tallaMinima"] . ')" id="girar" class="girar">&gt</p>
                ';

    echo '<div onclick="event.stopPropagation()" class="datos" style="display:none;">
                    <hr>
                </div>
            </div>';
}