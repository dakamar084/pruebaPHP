<?php
$correo = $_GET["correo"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="estilosContraseña.css">
</head>
<body>
    <div class="main">
        <p>Indica a continuación tu correo para poder modificar tu contraseña</p>
        <form id="formulario">
            <input type="email" name="correo" value="<?php echo $correo ?>" placeholder="tu correo aqui" id="correo">
            <input type="submit" value="Enviar">
        </form>
    </div>
    <div class="correoEnviado">
        <h1>por favor, introduzca a continuacion el codigo que se le ha enviado por correo para poder modificar la contraseña</h1>
        <form action="" id="formulario2">
            <input type="text" name="codigo" id="codigo1" class="codigo" maxlength="1">
            <input type="text" name="codigo" id="codigo2" class="codigo" maxlength="1">
            <input type="text" name="codigo" id="codigo3" class="codigo" maxlength="1">
            <input type="text" name="codigo" id="codigo4" class="codigo" maxlength="1">
            <input type="text" name="codigo" id="codigo5" class="codigo" maxlength="1">
            <input type="text" name="codigo" id="codigo6" class="codigo" maxlength="1">
            <input type="text" name="codigo" id="codigo7" class="codigo" maxlength="1">
            <input type="text" name="codigo" id="codigo8" class="codigo" maxlength="1">
            <input type="submit" value="Enviar">
        </form>
    </div>
    <div class="cambiarContraseña">
        <h1>Introduce la nueva contraseña</h1>
        <form action="" id="nuevaContra">
            <input type="password" name="contra1" id="contra1" placeholder="Nueva contraseña" autocomplete="off" required><br><br>
            <input type="password" name="contra2" id="contra2" placeholder="Repite la nueva contraseña" autocomplete="off" required><br>
            <p class="errores">

            </p>
            <input type="checkbox" name="verContraseñas" id="ver"><label for="ver">ver contraseñas</label><br>
            <input type="submit" value="Modificar contraseña" disabled title="primero debes escribir las contraseñas">
        </form>
    </div>
    <div class="codigoIncorrecto">
        <div>
            <img src="cerrar.png" alt="">
            <div class="mensajeError">
                <h1>El codigo que introduciste es erroneo</h1>
                <p>El codigo que introduciste no coincide con el enviado por correo. Por favor, vuelve a intentarlo</p>
            </div>
        </div>
    </div>
    <script src="contraseña.js"></script>
</body>
</html>