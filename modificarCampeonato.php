<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Recoger datos del formulario
    $id_camp = (int)($_POST["id_camp"] ?? 0);
    $nombre = $_POST["nombre"] ?? null;
    $localizacion = $_POST["localizacion"] ?? null;
    $enlace = $_POST["enlace"] ?? null;
    $categoria = $_POST["categoria"] ?? null;
    $fechaInicio = $_POST["fechaInicio"] ?? null;
    $libre = isset($_POST["libre"]) ? 1 : 0;
    $tallaMinima = (int)($_POST["tallaMinima"] ?? 0);
    $participacion = $_POST["participacion"] ?? null;
    $supervisor = $_POST["supervisor"] ?? null;
    $jornadas = (int)($_POST["numJornadas"] ?? 0);

    // Validar datos requeridos
    if (!$id_camp || !$nombre || !$localizacion || !$categoria || !$fechaInicio || !$supervisor || !$participacion || $jornadas < 0) {
        echo "Error: Faltan datos obligatorios o son inválidos.";
        exit;
    }

    // Iniciar transacción para asegurar consistencia
    $cone->begin_transaction();

    try {
        // Actualizar el campeonato
        $stm_camp = $cone->prepare("UPDATE campeonatos SET supervisor = ?, nombre = ?, localizacion = ?, enlaceMapa = ?, categoria = ?, open = ?, fechaInicio = ?, tallaMinima = ?, participacion = ? WHERE id_campeonato = ?");
        $stm_camp->bind_param("sssssisisi", $supervisor, $nombre, $localizacion, $enlace, $categoria, $libre, $fechaInicio, $tallaMinima, $participacion, $id_camp);
        $stm_camp->execute();

        // Obtener el número actual de jornadas
        $current_jornadas = $cone->query("SELECT COUNT(*) FROM jornadas WHERE id_campeonato = $id_camp")->fetch_row()[0];
        
        if ($jornadas > $current_jornadas) {
            // Añadir jornadas faltantes
            $to_add = $jornadas - $current_jornadas;
            for ($i = 0; $i < $to_add; $i++) {
                // Insertar jornada
                $stm_jornada = $cone->prepare("INSERT INTO jornadas (id_campeonato) VALUES (?)");
                $stm_jornada->bind_param("i", $id_camp);
                if (!$stm_jornada->execute()) {
                    throw new Exception("Error al añadir jornada: " . $stm_jornada->error);
                }
                $id_jornada = $cone->insert_id;

                // Insertar 2 mangas por jornada (como en el original)
                for ($j = 0; $j < 2; $j++) {
                    $stm_manga = $cone->prepare("INSERT INTO mangas (id_jornada, numParticipantes) VALUES (?, 20)");
                    $stm_manga->bind_param("i", $id_jornada);
                    if (!$stm_manga->execute()) {
                        throw new Exception("Error al añadir manga: " . $stm_manga->error);
                    }
                    $id_manga = $cone->insert_id;

                    // Insertar 2 turnos por manga (como en el original)
                    for ($k = 0; $k < 2; $k++) {
                        $stm_turno = $cone->prepare("INSERT INTO turnos (id_manga, numero_turno) VALUES (?, ?)");
                        $numero_turno = $k + 1;
                        $stm_turno->bind_param("ii", $id_manga, $numero_turno);
                        if (!$stm_turno->execute()) {
                            throw new Exception("Error al añadir turno: " . $stm_turno->error);
                        }
                    }
                }
            }
        } elseif ($jornadas < $current_jornadas) {
            // Eliminar jornadas sobrantes (y sus mangas/turnos por CASCADE)
            $to_remove = $current_jornadas - $jornadas;
            $stm_delete = $cone->prepare("DELETE FROM jornadas WHERE id_campeonato = ? ORDER BY id_jornada DESC LIMIT ?");
            $stm_delete->bind_param("ii", $id_camp, $to_remove);
            if (!$stm_delete->execute()) {
                throw new Exception("Error al eliminar jornadas sobrantes: " . $stm_delete->error);
            }
        }
        
        // Verificar y ajustar mangas y turnos para las jornadas existentes
        if ($jornadas > 0) {
            // Obtener todas las jornadas existentes
            $result = $cone->query("SELECT id_jornada FROM jornadas WHERE id_campeonato = $id_camp");
            while ($row = $result->fetch_assoc()) {
                $id_jornada = $row['id_jornada'];
                
                // Verificar mangas para esta jornada
                $manga_count = $cone->query("SELECT COUNT(*) FROM mangas WHERE id_jornada = $id_jornada")->fetch_row()[0];
                
                if ($manga_count < 2) {
                    // Añadir mangas faltantes
                    for ($j = $manga_count; $j < 2; $j++) {
                        $stm_manga = $cone->prepare("INSERT INTO mangas (id_jornada, numParticipantes) VALUES (?, 20)");
                        $stm_manga->bind_param("i", $id_jornada);
                        if (!$stm_manga->execute()) {
                            throw new Exception("Error al añadir manga: " . $stm_manga->error);
                        }
                        $id_manga = $cone->insert_id;
                        
                        // Añadir 2 turnos por manga
                        for ($k = 0; $k < 2; $k++) {
                            $stm_turno = $cone->prepare("INSERT INTO turnos (id_manga, numero_turno) VALUES (?, ?)");
                            $numero_turno = $k + 1;
                            $stm_turno->bind_param("ii", $id_manga, $numero_turno);
                            if (!$stm_turno->execute()) {
                                throw new Exception("Error al añadir turno: " . $stm_turno->error);
                            }
                        }
                    }
                } elseif ($manga_count > 2) {
                    // Eliminar mangas sobrantes (y sus turnos por CASCADE)
                    $to_remove = $manga_count - 2;
                    $stm_delete = $cone->prepare("DELETE FROM mangas WHERE id_jornada = ? ORDER BY id_manga DESC LIMIT ?");
                    $stm_delete->bind_param("ii", $id_jornada, $to_remove);
                    if (!$stm_delete->execute()) {
                        throw new Exception("Error al eliminar mangas sobrantes: " . $stm_delete->error);
                    }
                }
                
                // Verificar turnos para cada manga (2 por manga)
                $mangas = $cone->query("SELECT id_manga FROM mangas WHERE id_jornada = $id_jornada");
                while ($manga = $mangas->fetch_assoc()) {
                    $id_manga = $manga['id_manga'];
                    $turno_count = $cone->query("SELECT COUNT(*) FROM turnos WHERE id_manga = $id_manga")->fetch_row()[0];
                    
                    if ($turno_count < 2) {
                        // Añadir turnos faltantes
                        for ($k = $turno_count; $k < 2; $k++) {
                            $stm_turno = $cone->prepare("INSERT INTO turnos (id_manga, numero_turno) VALUES (?, ?)");
                            $numero_turno = $k + 1;
                            $stm_turno->bind_param("ii", $id_manga, $numero_turno);
                            if (!$stm_turno->execute()) {
                                throw new Exception("Error al añadir turno: " . $stm_turno->error);
                            }
                        }
                    } elseif ($turno_count > 2) {
                        // Eliminar turnos sobrantes
                        $to_remove = $turno_count - 2;
                        $stm_delete = $cone->prepare("DELETE FROM turnos WHERE id_manga = ? ORDER BY id_turno DESC LIMIT ?");
                        $stm_delete->bind_param("ii", $id_manga, $to_remove);
                        if (!$stm_delete->execute()) {
                            throw new Exception("Error al eliminar turnos sobrantes: " . $stm_delete->error);
                        }
                    }
                }
            }
        }

        // Confirmar transacción
        $cone->commit();
        echo "Campeonato modificado correctamente";
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $cone->rollback();
        echo "Error: " . $e->getMessage();
    }
}
