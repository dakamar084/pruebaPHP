<?php

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $camp = $_GET["campeonato"];
    $talla = $_GET["talla"];
    session_start();
    $correo = $_SESSION["correo"];

    $stm_piezas = $cone->prepare("SELECT longitud from piezas where id_participante in
(SELECT id_participante from participantes_turnos where id_participante in
(SELECT id_participante from participantes where correo like ?)) AND

id_turno in(SELECT id_turno from participantes_turnos where id_turno in
       		(SELECT id_turno from turnos where id_manga in
             (SELECT id_manga from mangas where id_jornada IN
             (SELECT id_jornada from jornadas where id_campeonato = ?)))) order by longitud DESC;");

    $stm_piezas->bind_param("ss", $correo, $camp);

    $stm_piezas->execute();
    $result = $stm_piezas->get_result();

    echo '</ul></p><hr><p style="font-style:italic;">* las piezas en rojo no superaron la talla minima para ese campeonato, las verdes si lo hicieron</p>';
    echo '<p class="piezasP">tus piezas: </p><ul>';
    echo $result->num_rows == 0 ? '<p style="margin-left:-30pt; color:red;"> no tienes piezas todavia</p>' : "";
    while ($row = $result->fetch_assoc()) {
        $color = $row["longitud"] >= $talla ? "green" : "red";
        echo '<li style="color:' . $color . ';">' . $row["longitud"] . " cm</li>";
    }
    echo '</ul>';

    $stm_usuario = $cone->prepare("SELECT 
                                        pt.cargo,pt.id_turno,
                                        (@turno_num := @turno_num + 1) AS numero_turno,
                                        c.turnoActual,pt.pareja as id_pareja,
                                        p.nombre AS pareja
                                      FROM participantes_turnos pt
                                    INNER JOIN turnos t ON pt.id_turno = t.id_turno
                                    INNER JOIN mangas m ON t.id_manga = m.id_manga
                                    INNER JOIN jornadas j ON j.id_jornada = m.id_jornada
                                    INNER JOIN campeonatos c ON j.id_campeonato = c.id_campeonato
                                    INNER JOIN participantes p ON pt.pareja = p.id_participante
                                    JOIN (SELECT @turno_num := 0) AS init -- Inicializa la variable de turno
                                    WHERE pt.id_participante IN (SELECT id_participante 
                                                                FROM participantes 
                                                                WHERE correo LIKE ?)
                                    AND j.id_campeonato = ?
                                    ORDER BY m.id_manga, t.numero_turno;");

    $stm_usuario->bind_param("si", $correo, $camp);
    $stm_usuario->execute();

    $res = $stm_usuario->get_result();

    if ($_GET["estado"] == "activo") {
        echo $res->num_rows == 0 ? "<p>aun no se ha realizado el reparto de roles</p>" : "<p>reparto de roles: </p>";
        echo '<ol class="lista-turnos">';
        while ($row = $res->fetch_assoc()) {
            $clase = $row["numero_turno"] == $row["turnoActual"] ? "turno-actual" : "turno-item";
            echo '
        <li class="' . $clase . '" id="' . $clase . '">
        <ul class="turno-detalles">
        <li data-id="' . $row["id_turno"] . '" class="turno"><strong>Turno:</strong> ' . $row["numero_turno"] . '</li>
        <li class="cargo"><strong>Cargo:</strong> ' . $row["cargo"] . '</li>
        <li class="pareja" data-id="' . $row["id_pareja"] . '"><strong>Pareja:</strong> ' . $row["pareja"] . '</li>
        </ul>
        </li>
        ';
        }
        echo '</ol>';
    } else {
        echo '<p style="font-style:italic;">La información de los turnos solo esta disponible cuando los campeonatos estan activos';
    }


    $stm_ranking = $cone->prepare("SELECT 
    pa.correo, 
    SUM(
        CASE
            WHEN p.longitud < c.tallaMinima THEN 15
            ELSE 100 + (20 * p.longitud)
        END
    ) AS suma
FROM participantes_turnos pt
INNER JOIN participantes pa ON pa.id_participante = pt.id_participante
INNER JOIN piezas p ON p.id_turno = pt.id_turno AND p.id_participante = pt.id_participante
INNER JOIN turnos t ON t.id_turno = pt.id_turno
INNER JOIN mangas m ON m.id_manga = t.id_manga
INNER JOIN jornadas j ON j.id_jornada = m.id_jornada
INNER JOIN campeonatos c ON c.id_campeonato = j.id_campeonato
WHERE c.id_campeonato = ?
GROUP BY pa.correo
ORDER BY suma DESC;");

    $stm_ranking->bind_param("i", $camp);

    $stm_ranking->execute();

    $result = $stm_ranking->get_result();

    echo '<p>ranking:</p><ol>';
    echo $result->num_rows == 0 ? '<p style="margin-left:-30pt;color:red;">no hay clasificacion todavia</p>' : "";


    while ($row = $result->fetch_assoc()) {
        $estilo = $correo == $row["correo"] ? "bold" : "normal";
        echo '<li style="font-weight:' . $estilo . '">' . $row["correo"] . '→' . $row["suma"] . '</li>';
    }
    echo "</ol>";

}