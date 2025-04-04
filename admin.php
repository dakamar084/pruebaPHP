<?php
session_start();

require 'conexion.php';


if(!isset($_SESSION['correo'])){
    header('Location:index.html');
}

$correo = $_SESSION["correo"];

$stm = $cone->prepare("SELECT rol from participantes where correo like ?");
$stm->bind_param('s', $correo);
$stm->execute();
$res = $stm->get_result();
$rol = $res->fetch_assoc()["rol"];

if ($rol !== "admin") {
    session_abort();
    header("refresh:0.5; $rol.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>vista de admin</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <div class="botones">
        <button id="addCamp">Añadir campeonato</button>
        <select id="campeonatos" size="1">
        </select>
        <button id="tablaParticipantes">Ver participantes</button>
    </div>
    <div class="otraParte">
        <form action="">
            <input type="hidden" name="id_camp" value="1">
            <input type="text" placeholder="nombre" name="nombre" required>
            <input type="text" placeholder="localizacion" name="localizacion" required>
            <input type="text" placeholder="enlace a mapa de points" name="enlace" required>
            <div>
                <span>categoria: </span>
                <input type="radio" name="tipo" value="juvenil" id="juvenil"><label for="juvenil">Juvenil</label>
                <input type="radio" name="tipo" value="absoluta" id="absoluta"><label for="absoluta">Absoluta</label>
            </div>
            <div>
                <span>participacion: </span>
                <select name="participacion" required>
                    <option value="individual">Individual</option>
                    <option value="duos">Dúos</option>
                    <option value="seleccion">Selección</option>
                </select>
            </div>
            <div>
                <span for="toggle" style="margin-bottom: -5pt;">Participacion Libre: </span>
                <div class="switch">
                    <input type="checkbox" name="libre" id="toggle"><label for="toggle"></label>
                </div>
            </div>
            <div class="botonesJornadas">
                <button class="addJornada">Añadir Jornada</button>
                <button class="delJornada">Eliminar Jornada</button>
            </div>
            <div>
                <label for="fecha">fecha de comienzo del campeonato</label>
                <input type="date" name="fechaInicio" required placeholder="" name="" id="fecha">
            </div>
            <input type="number" required placeholder="talla minima" name="tallaMinima" id="" min="0">
            <input type="submit" id="botonFormu" value="Añadir">
        </form>
        <div class="derecha">
            <select name="jornadas" id="jornadas" size="4">
                <option value="jornada1">jornada 1</option>
                <option value="jornada2">jornada 2</option>
            </select>
            <div class="ListaSupervisores">
                <select name="supervisor" id="supervisor" size="4">
                </select>
                <button title="añadir un nuevo supervisor">Añadir supervisor</button>
            </div>
        </div>
        <div class="añadirOrganizador" style="display:none;">
            <img src="cerrar.png" alt="">
            <form action="">
                <input type="text" name="nombre" id="nombre" placeholder="nombre del supervisor">
                <input type="email" id="correo" placeholder="correo" required>
                <p class="mensaje" style="color:red;"></p>
                <input type="password" placeholder="contraseña" id="contraSuper" required
                    autocomplete="current_password">
                <p id="mensajesContra"></p>
                <input type="submit" id="add" value="Añadir">
            </form>
        </div>
    </div>
    <div class="tablaParticipantes">
        
    </div>
    <script src="admin.js">
    </script>
</body>
</html>