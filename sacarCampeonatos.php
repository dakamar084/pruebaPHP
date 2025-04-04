<?php

require 'conexion.php';

$hoy = date("Y-m-d");

$stm = $cone -> prepare("SELECT id_campeonato, nombre from campeonatos where fechaInicio >= ?");
$stm -> bind_param("s", $hoy);

$stm -> execute();

$res = $stm -> get_result();

echo '<option value="def">Modificar Campeonato</option>';
while($row = $res -> fetch_assoc()) {
    echo '<option value="'.$row["id_campeonato"].'">'.$row["nombre"].'</option>';
}