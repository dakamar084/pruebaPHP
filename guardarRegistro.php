<?php

require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos del formulario
    session_start();


    $nombre = explode(" ", $_POST["nombre"])[0] ?? '';
    $apellidos = explode(" ", $_POST["nombre"])[1] .' '. explode(" ", $_POST["nombre"])[2] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $cp = $_POST['cp'] ?? '';
    $fechaNac = $_POST['fecha_nacimiento'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $contra = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT); // Encriptar contraseña
    $numLicencia = $_POST['licencia'] ?? ''; // Cambiar de numLicencia a licencia
    $numFede = $_POST['federativa'] ?? '';  // Cambiar de numFede a federativa
    $permisos = $_POST["permisos"];
    $correo = $_POST["correo"];
    $provincia = $_POST["provincia"];
    $endpoint = $_POST["endpoint"] == "null" ? null : $_POST["endpoint"];

    // Manejo de imágenes
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $notis = $permisos[0];
    $interes = $permisos[1];

    $imagenPath = '';
    if (!empty($_FILES['imagen']['name'])) {
        // Obtener la extensión del archivo
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        // Generar el nombre del archivo con uniqid() después del nombre original
        $imagenPath = $uploadDir . pathinfo($_FILES['imagen']['name'], PATHINFO_FILENAME) . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $imagenPath);
    }
    
    // Insertar datos en la base de datos
    $stmt = $cone->prepare("INSERT INTO participantes (nombre, apellidos, correo, provincia, telefono, direccion, CP, fechaNac, rutaImagen, pais, contraseña, numLicencia, numFede, interes, notificaciones,verificado, endpoint) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,0, ?)");
    $stmt->bind_param("sssssssssssssiis", $nombre, $apellidos, $correo,$provincia, $telefono, $direccion, $cp, $fechaNac, $imagenPath, $pais, $contra, $numLicencia, $numFede, $interes, $notis, $endpoint);
    
    if ($stmt->execute()) {
        echo "Registro exitoso";
    } else {
        echo "Error en el registro: " . $stmt->error;
    }
    $_SESSION["correo"] = $correo;
    $stmt->close();
    $cone->close();
} else {
    echo "Método no permitido";
}

?>