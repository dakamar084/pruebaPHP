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
$stm = $cone->prepare("SELECT * from participantes where correo like ?");
$stm->bind_param("s", $correo);
$stm->execute();
$result = $stm->get_result();
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor <?php echo htmlspecialchars($_SESSION["correo"]); ?></title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisor.css">
</head>

<body>
    <div id="main"></div>
    <div id="modal"></div>
    <div class="perfil">
        <img src="super.png" alt="icono de perfil">
        <div class="botones" style="display:none;">
            <button id="modDatos">Modificar mis datos</button>
            <button id="cerrarSesion">Cerrar sesion</button>
        </div>
    </div>
    <div class="modalDatos">
        <div class="mover"></div>
        <img src="cerrar.png" alt="">
        <select name="aEditar" id="combo" style="display: block;">
            <option value="personales" class="actual" id="cargarDatos">datos Personales</option>
            <option value="direccion" class="no" id="cargarDireccion">direccion</option>
            <option value="otros" class="no" id="cargarOtros">otros datos</option>
        </select>
        <div class="personales">
            <form action="">
                <input type="text" name="nombre" placeholder="Nombre Completo" pattern="(\w+\s){2,}\w+"
                    title="Debe contener al menos tres palabras (dos espacios)"
                    value="<?php echo $row["nombre"] . " " . $row["apellidos"] ?>" required>
                <input type="email" name="correo" readonly placeholder="Correo Electrónico"
                    value="<?php echo $row["correo"]; ?>" required>
                <input type="text" id="telefono" maxlength="9" name="telefono" value="<?php echo $row["telefono"] ?>"
                    placeholder="Teléfono" pattern="^[679]\d{8}$" title="Debe tener 9 dígitos y empezar con 6, 7 o 9"
                    required>
                <label for="fecha">Fecha de nacimiento</label>
                <input type="date" name="fecha_nacimiento" id="fecha" value="<?php echo $row["fechaNac"] ?>" required>
                <input type="submit" value="Modificar" id="botonRegistro">
            </form>
        </div>
        <div class="direccion">
            <form action="">
                <input type="text" name="direccion" placeholder="Dirección" value="<?php echo $row["direccion"] ?>"
                    required>
                <input type="text" name="cp" id="telefono" maxlength="5" value="<?php echo $row["CP"] ?>"
                    placeholder="Código Postal" required>
                <input type="text" name="provincia" placeholder="Provincia" value="<?php echo $row["provincia"] ?>"
                    required>
                <input type="text" name="pais" placeholder="País" value="España" value="<?php echo $_row["pais"] ?>"
                    required>
                <input type="submit" value="Modificar">
            </form>
        </div>
        <div class="otros">
            <form>
                <input type="text" name="licencia" value="<?php echo $row["numLicencia"] ?>" id=""
                    placeholder="Nº de licencia">
                <input type="text" name="federativa" id="" value="<?php echo $row["numFede"]; ?>"
                    placeholder="Nº de federativa">
                <div>
                    <input type="checkbox" name="permisos[]" value="notificaciones" <?php echo $row["notificaciones"] == 1 ? "checked" : "" ?> id="notis"><label for="notis">Doy permiso para que se me envien
                        notificaciones
                        sobre mi categoria</label><br>
                    <input type="checkbox" name="permisos[]" <?php echo $row["interes"] == 1 ? "checked" : "" ?>
                        value="interes" id="interes"><label for="interes">Doy permiso a que se me envie informacion de
                        interes</label>
                </div>
                <input type="submit" value="Modificar">
            </form>
        </div>
    </div>
    <script src="supervisor.js"></script>
</body>

</html>