<?php

require 'conexion.php';

$stm = $cone ->prepare('SELECT concat(nombre, \'-\', correo) as supervisor from participantes where rol like "supervisor"');

$stm -> execute();

$result = $stm ->get_result();
$primero = true;
while($row = $result -> fetch_assoc()){
    $correo = explode("-", $row["supervisor"])[1];
    $selected = $primero ? "selected" : "";
    echo '<option value="'.$correo.'" '.$selected.'>'.$row["supervisor"].'</option>';
    $primero = false;
}
