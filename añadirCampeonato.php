<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Recoger datos del formulario
    $nombre = $_POST["nombre"] ?? null;
    $jornadas = (int)($_POST["numJornadas"] ?? 0); // Convertir a entero
    $localizacion = $_POST["localizacion"] ?? null;
    $enlace = $_POST["enlace"] ?? null;
    $categoria = $_POST["categoria"] ?? null;
    $fechaInicio = $_POST["fechaInicio"] ?? null;
    $libre = isset($_POST["libre"]) ? 1 : 0;
    $tallaMinima = (int)($_POST["tallaMinima"] ?? 0); // Convertir a entero
    $participacion = $_POST["participacion"] ?? null;
    $supervisor = $_POST["supervisor"] ?? null;

    $fechaFinDate = new DateTime($fechaInicio);
    $fechaFinDate -> modify('+ '.$jornadas.' days');
    $fechaFin = $fechaFinDate -> format("Y-m-d");
    // Validar datos requeridos
    if (!$nombre || !$jornadas || !$localizacion || !$categoria || !$fechaInicio || !$supervisor || !$participacion) {
        echo "Error: Faltan datos obligatorios.";
        exit;
    }

    // Iniciar transacción para asegurar consistencia
    $cone->begin_transaction();

    try {
        // Insertar el campeonato
        $stm = $cone->prepare("INSERT INTO campeonatos (supervisor, nombre, localizacion, enlaceMapa, categoria, open, fechaInicio, tallaMinima, participacion, fechaFin) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");
        $stm->bind_param("sssssisiss", $supervisor, $nombre, $localizacion, $enlace, $categoria, $libre, $fechaInicio, $tallaMinima, $participacion, $fechaFin);
        
        if (!$stm->execute()) {
            throw new Exception("Error al añadir el campeonato: " . $stm->error);
        }

        $id_campeonato = $cone->insert_id; // Obtener el ID del campeonato recién creado

        // Insertar jornadas, mangas y turnos
        for ($i = 0; $i < $jornadas; $i++) {
            // Insertar jornada
            $stm_jornada = $cone->prepare("INSERT INTO jornadas (id_campeonato) VALUES (?)");
            $stm_jornada->bind_param("i", $id_campeonato);
            if (!$stm_jornada->execute()) {
                throw new Exception("Error al añadir jornada: " . $stm_jornada->error);
            }
            $id_jornada = $cone->insert_id;

            // Insertar 2 mangas por jornada
            for ($j = 0; $j < 2; $j++) {
                $stm_manga = $cone->prepare("INSERT INTO mangas (id_jornada, numParticipantes) VALUES (?, 20)");
                $stm_manga->bind_param("i", $id_jornada);
                if (!$stm_manga->execute()) {
                    throw new Exception("Error al añadir manga: " . $stm_manga->error);
                }
                $id_manga = $cone->insert_id;

                // Insertar 2 turnos por manga
                for ($k = 0; $k < 2; $k++) {
                    $stm_turno = $cone->prepare("INSERT INTO turnos (id_manga, numero_turno) VALUES (?, ?)");
                    $numero_turno = $k + 1; // Turno 1 y 2
                    $stm_turno->bind_param("ii", $id_manga, $numero_turno);
                    if (!$stm_turno->execute()) {
                        throw new Exception("Error al añadir turno: " . $stm_turno->error);
                    }
                }
            }
        }

        // Confirmar transacción
        $cone->commit();
        echo "Campeonato añadido correctamente.";
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $cone->rollback();
        echo "Error: " . $e->getMessage();
    }
}