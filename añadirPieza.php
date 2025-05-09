<?php 

require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $tamaño =(int) $_POST["tamaño"];
    $participante = (int)$_POST["participante"];
    $turno = (int)$_POST["turno"];

    $stm = $cone -> prepare("INSERT into piezas(id_participante, id_turno, longitud) values(?,?,?)");
    $stm -> bind_param("iii", $participante, $turno, $tamaño);
    $stm -> execute();

                    
    echo $stm -> affected_rows >= 1 ? "pieza añadida correctamente" : "hubo un error al añadir la pieza";
}