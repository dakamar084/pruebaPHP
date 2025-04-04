<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $campeonato = $_GET["campeonato"] ?? null;
    if (!$campeonato) {
        echo "Error: Campeonato no especificado";
        exit;
    }

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
                <p>' . htmlspecialchars($row["correo"]) . '</p>
                <img src="cerrar.png" data-correo="' . htmlspecialchars($row["correo"]) . '" data-campeonato="' . htmlspecialchars($campeonato) . '"/>
              </div><hr>';
    }
    echo '</div>
    </div>';
}