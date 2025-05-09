<?php
require 'conexion.php';

ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $body = json_decode(file_get_contents("php://input"), true);
    if ($body === null) {
        echo "Error: Datos no recibidos";
        exit;
    }

    $tipo = $body["tipo"];

    if ($tipo == "individual") {
        $consulta = $body["categoria"] == "absoluta" ?
            "SELECT * FROM participantes where rol like 'cliente'" :
            "SELECT * FROM participantes WHERE TIMESTAMPDIFF(YEAR, fechaNac, CURDATE()) < 21 and rol like 'cliente'";
        $stm = $cone->prepare($consulta);
        $stm->execute();
        $result = $stm->get_result();


        $yaSorteo = $cone->prepare(query: "SELECT cargo FROM participantes_turnos where id_turno in
                                        (SELECT id_turno from turnos where id_manga in
                                        (SELECT id_manga from mangas where id_jornada in
                                        (SELECT id_jornada from jornadas where id_campeonato=?)))
                                        order by rand() desc LIMIT 1;");
        $yaSorteo->bind_param("i", $body["campeonato"]);
        $yaSorteo->execute();
        $res = $yaSorteo->get_result()->fetch_assoc()["cargo"] == null ? "false" : "true";

        $stm_turno = $cone->prepare("SELECT turnoActual from campeonatos where id_campeonato = ?");
        $stm_turno->bind_param("i", $body["campeonato"]);
        $stm_turno->execute();
        $stm_turno->bind_result($turno);
        $stm_turno->fetch();
        echo '<h3>CONFIGURACIÓN DEL CAMPEONATO</h3>';
        echo '<img src="cerrar.png" alt="icono cerrar"/>';
        echo '<select id="listaParticipantes" data-puede="true"><option value="def">Participantes de esta categoría</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row["correo"]) . '">' . htmlspecialchars($row["correo"]) . '</option>';
        }

        echo '</select>';
        $title = $res == "true" ? "ya se ha realizado el sorteo de cargos" : "debes seleccionar un participante para añadirlo a la lista";
        echo '<button disabled title="' . $title . '" data-id="' . $body["campeonato"] . '">Añadir participante</button>';
        echo '<p>Lista de participantes:</p>';
        echo '<div class="participantes"> </div>';
        echo '<p>turno actual:<span class="turno">' . $turno . '</span></p>';
        echo '<div class="accion" data-sorteado="' . $res . '"></div>';
    } else {
        $consulta = $body["categoria"] == "absoluta" ?
            "SELECT distinct e.nombre from equipo e
            INNER JOIN jugadores_equipo je on je.id_equipo = e.id_equipo
            INNER JOIN participantes p on p.id_participante = je.id_participante
            where p.rol like 'cliente' and e.tipoEquipo like ?" :

            "SELECT distinct e.nombre from equipo e
            INNER JOIN jugadores_equipo je on je.id_equipo = e.id_equipo
            INNER JOIN participantes p on p.id_participante = je.id_participante
            where p.rol like 'cliente' and TIMESTAMPDIFF(YEAR, p.fechaNac, CURDATE()) < 21 and e.tipoEquipo like ?; ";
        $stm = $cone->prepare($consulta);
        $stm -> bind_param("s", $tipo);
        $stm->execute();
        $result = $stm->get_result();


        $yaSorteo = $cone->prepare(query: "SELECT cargo FROM participantes_turnos where id_turno in
                                        (SELECT id_turno from turnos where id_manga in
                                        (SELECT id_manga from mangas where id_jornada in
                                        (SELECT id_jornada from jornadas where id_campeonato=?)))
                                        order by rand() desc LIMIT 1;");
        $yaSorteo->bind_param("i", $body["campeonato"]);
        $yaSorteo->execute();
        $res = $yaSorteo->get_result()->fetch_assoc()["cargo"] == null ? "false" : "true";
        
        $stm_turno = $cone->prepare("SELECT turnoActual from campeonatos where id_campeonato = ?");
        $stm_turno->bind_param("i", $body["campeonato"]);
        $stm_turno->execute();
        $stm_turno->bind_result($turno);
        $stm_turno->fetch();
        echo '<h3>CONFIGURACIÓN DEL CAMPEONATO</h3>';
        echo '<img src="cerrar.png" alt="icono cerrar"/>';
        echo '<select id="listaParticipantes" data-puede="true"><option value="def">Participantes de esta categoría</option>';
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . htmlspecialchars($row["nombre"]) . '">' . htmlspecialchars($row["nombre"]) . '</option>';
        }

        
        echo '</select>';
        $title = $res == "true" ? "ya se ha realizado el sorteo de cargos" : "debes seleccionar un participante para añadirlo a la lista";
        echo '<button disabled title="' . $title . '" data-id="' . $body["campeonato"] . '">Añadir participante</button>';
        echo '<a href="registro.html" target="_blank">¿No aparece en la lista? dale de alta aqui</a>';
        echo '<p>Lista de participantes:</p>';
        echo '<div class="participantes"> </div>';
        echo '<p>turno actual:<span class="turno">' . $turno . '</span></p>';
        echo '<div class="accion" data-sorteado="' . $res . '"></div>';
    }
}