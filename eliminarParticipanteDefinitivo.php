<?php
require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "GET"){
    $id_participante = $_GET["participante"];
    $stm = $cone -> prepare("DELETE from participantes where id_participante = ?");
    $stm -> bind_param("i", $id_participante);
    $stm -> execute();
    echo $stm -> affected_rows >= 1 ? "participante eliminado correctamente": "hubo un error al eliminar al participante";
}