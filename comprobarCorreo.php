<?php

require 'conexion.php';

header("Content-Type: application/json");

$correo = $_GET["correo"];

$stm = $cone -> prepare("SELECT * from participantes where correo like ?");
$stm -> bind_param("s", $correo);
$stm -> execute();
$result = $stm -> get_result();

echo $result -> num_rows == 1 ? json_encode(["existe"=>true,"mensaje"=>"ese correo ya pertenece a un usuario"]):json_encode(["existe"=>false,"mensaje"=> ""]);