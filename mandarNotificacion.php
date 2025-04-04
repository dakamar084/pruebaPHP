<?php

header('Content-Type: application/json');
require 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 📌 Firebase Configuration
$projectId = 'pesca-88b61'; // Tu ID de proyecto
$fcmUrl = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
$serviceAccountPath = 'service-account.json'; // Ruta a tu archivo de cuenta de servicio

// 📌 Leer los datos del POST
$input = json_decode(file_get_contents("php://input"), true);

$notificacion = json_decode($input["notificacion"],true);
if (!$input || !isset($input['destinatario'], $notificacion['titulo'], $notificacion['body'])) {
    die(json_encode(["error" => "Faltan parámetros en la petición."]));
}

$correoDestinatario = $input['destinatario'];
$tituloNotificacion = $notificacion['titulo'];
$mensajeNotificacion = $notificacion['body'];
$linkNotificacion = $notificacion['link'];

// 📌 Obtener el endpoint del destinatario
$stm = $cone->prepare("SELECT endpoint, notificaciones FROM participantes WHERE correo = ?");
$stm->bind_param("s", $correoDestinatario);
$stm->execute();
$res = $stm->get_result();
$data = $res->fetch_assoc();

if (!$data) {
    die(json_encode(["error" => "El destinatario no está registrado."]));
}
$endpoint = $data["endpoint"];
$permiso = $data["notificaciones"];

// 📌 Verificar si el usuario tiene permisos para recibir notificaciones
if ($permiso != 1) {
    die(json_encode(["error" => "El destinatario no tiene permisos para recibir notificaciones."]));
}

// 📌 Obtener el token de acceso para Firebase
$serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
$accessToken = getAccessToken($serviceAccount);

if (!$accessToken) {
    die(json_encode(["error" => "No se pudo obtener el token de acceso."]));
}

// 📌 Preparar el mensaje para enviar
// 📌 Preparar el mensaje para enviar (Formato correcto para API v1)
$mensaje = [
    "message" => [  // Estructura correcta para FCM v1
        "token" => $endpoint,  // Cambiado de "to" a "token"
        "notification" => [
            "title" => $tituloNotificacion,
            "body" => $mensajeNotificacion
        ],
        "data" => [  // click_action debe ir en data
            "click_action" => $linkNotificacion
        ],
        "webpush" => [  // Configuración específica para web
            "fcm_options" => [
                "link" => $linkNotificacion  // Para que funcione el clic en web
            ]
        ]
    ]
];

// 📌 Enviar la notificación
$response = sendRequest($fcmUrl, $accessToken, $mensaje);

// 📌 Devolver respuesta
echo json_encode(["mensaje_enviado" => true, "respuesta" => json_decode($response, true)]);

// 🔥 Funciones auxiliares

function getAccessToken($serviceAccount) {
    $url = 'https://oauth2.googleapis.com/token';
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    $data = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => generateJWT($serviceAccount)
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, value: true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        die(json_encode(["error" => "Error cURL: " . curl_error($ch)]));
    }

    curl_close($ch);
    $response = json_decode($response, true);
    
    return $response['access_token'] ?? null;
}

function generateJWT($serviceAccount) {
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claim = [
        'iss' => $serviceAccount['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => 'https://oauth2.googleapis.com/token',
        'exp' => time() + 3600,
        'iat' => time()
    ];

    $privateKey = $serviceAccount['private_key'];
    $key = openssl_pkey_get_private($privateKey);
    if (!$key) {
        die(json_encode(["error" => "Error en clave privada: " . openssl_error_string()]));
    }

    $jwtHeader = base64UrlEncode(json_encode($header));
    $jwtClaim = base64UrlEncode(json_encode($claim));
    $signature = generateSignature("$jwtHeader.$jwtClaim", $key);

    return "$jwtHeader.$jwtClaim." . base64UrlEncode($signature);
}

function generateSignature($data, $privateKey) {
    if (!openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
        die(json_encode(["error" => "Error al generar la firma: " . openssl_error_string()])); 
    }
    return $signature;
}

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function sendRequest($url, $accessToken, $data) {
    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>
