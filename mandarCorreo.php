<?php

require 'conexion.php';
require 'vendor/autoload.php'; // Autoload de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === "POST") {

  // Remitente fijo
  $remitenteCorreo = 'dacamar084@gmail.com';
  $remitenteNombre = 'Soporte Técnico de Pesca';
  $remitentePass = 'vssk stto sfrw xmch'; // Usa una App Password si Gmail tiene 2FA

  // Datos que llegan por POST
  $correos = $_POST['correos'] ?? [];
  $subject = $_POST['asunto'] ?? 'Sin asunto';
  $mensajeHTML = $_POST['mensaje'] ?? '<p>Mensaje vacío</p>';

  // Inicializa PHPMailer
  $mail = new PHPMailer(true);


  // Enviar a cada destinatario
  foreach ($correos as $correo) {
    try {
      // Configuración del servidor SMTP
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com';
      $mail->SMTPAuth = true;
      $mail->Username = $remitenteCorreo;
      $mail->Password = $remitentePass;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;
      $mail -> CharSet = 'UTF_8';

      // Configuración del remitente
      $mail->setFrom($remitenteCorreo, $remitenteNombre);
      $mail->isHTML(true);
      $mail->addAddress($correo);
      $mail->Subject = $subject;
      $mail->Body = $mensajeHTML;

      $mail->send();
      $mail->clearAddresses(); // Limpiar destinatarios para el siguiente

    } catch (Exception $e) {
      echo "Error al enviar correo: {$mail->ErrorInfo}";
    }

    echo "Correos enviados correctamente.";
  }
}
