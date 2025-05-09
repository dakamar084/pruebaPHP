<?php
header("Content-Type:text/html;charset=UTF-8");
$caracteres = "abcdefghijklmopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-!";
$cant = 0;
$contraseña = "";
while($cant != 8){
    $posicion = rand(0, strlen($caracteres));
    $caracter = substr($caracteres, $posicion, 1);
    $contraseña .= $caracter;
    $cant++;
}
session_start();
$_SESSION["codigo"] = $contraseña;
echo $contraseña;
