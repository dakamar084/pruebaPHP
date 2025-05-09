<?php

require 'conexion.php';

session_start();

$stm = $cone -> prepare("SELECT * from campeonatos where supervisor like ?");

$stm -> bind_param("s", $_SESSION["correo"]);
$stm -> execute();
$stm -> store_result();

echo $stm -> num_rows;