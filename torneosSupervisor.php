<?php
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    session_start();
    if (!isset($_SESSION["correo"])) {
        echo "Error: Sesión no iniciada";
        exit;
    }

    $supervisor = $_SESSION["correo"];
    $stm = $cone->prepare("SELECT * FROM campeonatos WHERE supervisor LIKE ?");
    $stm->bind_param("s", $supervisor);
    $stm->execute();
    $result = $stm->get_result();
    if($result -> num_rows == 0){
        echo '<p></p><h1>No tienes asinado ningún campeonato todavía</h1>';
    }
    while ($row = $result->fetch_assoc()) {
        echo '<button class="torneo" title="modificar los participantes de '.$row["nombre"].'" data-tipo="'.$row["participacion"].'" data-id="' . htmlspecialchars($row["id_campeonato"]) . '" data-categoria="' . htmlspecialchars($row["categoria"]) . '">' . htmlspecialchars($row["nombre"]) . '</button>';
    }
}
