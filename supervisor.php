<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['correo'])) {
    header('Location: index.html');
    exit;
}

$correo = $_SESSION["correo"];
$stm = $cone->prepare("SELECT rol FROM participantes WHERE correo LIKE ?");
$stm->bind_param('s', $correo);
$stm->execute();
$res = $stm->get_result();
$rol = $res->fetch_assoc()["rol"];

if ($rol !== "supervisor") {
    session_abort();
    header("refresh:0.5; url=$rol.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor <?php echo htmlspecialchars($_SESSION["correo"]); ?></title>
    <link rel="stylesheet" href="supervisor.css">
</head>
<body>
    <div id="main"></div>
    <div id="modal"></div>
    <script src="supervisor.js"></script>
</body>
</html>