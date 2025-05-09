<?php

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $body = json_decode(file_get_contents("php://input"), true);
    session_start();
    $correo = $_SESSION["correo"];
    if($body["tipo"] != "individual"){
        $cant = $body["tipo"] == "duos" ? 2 : 4;
        $stm_equipo = $cone -> prepare("SELECT * from jugadores_equipo where id_equipo in(SELECT id_equipo from equipo where nombre like ?)");
        $stm_equipo -> bind_param("s", $body["correo"]);
        $stm_equipo -> execute();
        $stm_equipo -> store_result();
        if($stm_equipo -> num_rows < $cant){
            die("el equipo seleccionado no tiene todavia los participantes necesarios");
        }
    }
    $cantidad = sizeof(explode("@", $body["correo"]));
    $consulta = $cantidad != 1 ? "INSERT INTO participantes_turnos (id_participante, id_turno)
                                            SELECT 
                                                (SELECT id_participante FROM participantes WHERE correo LIKE ? LIMIT 1),
                                                t.id_turno
                                            FROM turnos t
                                            WHERE t.id_manga IN (
                                                SELECT id_manga 
                                                FROM mangas 
                                                WHERE id_jornada IN (
                                                    SELECT id_jornada 
                                                    FROM jornadas 
                                                    WHERE id_campeonato = ?
                                                )
                                            )
                                            ON DUPLICATE KEY UPDATE id_participante = id_participante;"
                                            
                                            : "INSERT ignore INTO participantes_turnos (id_participante, id_turno)
                                        SELECT 
                                            je.id_participante,
                                            t.id_turno
                                        FROM jugadores_equipo je
                                        JOIN equipo e ON e.id_equipo = je.id_equipo
                                        JOIN turnos t on 1=1
                                        JOIN mangas m ON m.id_manga = t.id_manga
                                        JOIN jornadas j ON j.id_jornada = m.id_jornada
                                        WHERE e.nombre LIKE ?
                                        AND j.id_campeonato = ?";
            $stm = $cone -> prepare($consulta);
            $stm -> bind_param("si",$body["correo"],$body["campeonato"]);
            $stm -> execute();
            $stm -> get_result();
    echo $stm -> affected_rows >= 1 ? "participante añadido correctamente" : "este participante ya estaba añadido";
}
