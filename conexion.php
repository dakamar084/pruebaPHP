<?php

$host = "localhost";
$db = "pesca";
$user = "root";
$passwd = "";

$cone = new mysqli($host, $user, $passwd, $db);
if ($cone->connect_errno) {
    die("error al conectar con la base de datos");
}