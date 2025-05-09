<?php

header('Content-Type: application/json');
require 'conexion.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();


// Firebase Configuration
$projectId = 'pesca-88b61';
$fcmUrl = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
$serviceAccountPath = './/service-account.json';

// Cargar archivo de servicio
$serviceAccountContent = file_get_contents($serviceAccountPath);
if ($serviceAccountContent === false) {
    die(json_encode(["error" => "No se pudo leer el archivo service-account.json"]));
}
$serviceAccount = json_decode($serviceAccountContent, true);
if ($serviceAccount === null) {
    die(json_encode(["error" => "Error al decodificar JSON", "details" => json_last_error_msg()]));
}

// Leer los datos del POST
$input = json_decode(file_get_contents("php://input"), true);
$notificacion = json_decode($input["notificacion"], true);
if (!$input || !isset($notificacion['titulo'], $notificacion['body'])) {
    die(json_encode(["error" => "Faltan parámetros en la petición."]));
}

$correoDestinatario = $input['destinatario'] ?? "";
$tituloNotificacion = $notificacion['titulo'];
$mensajeNotificacion = $notificacion['body'];
$linkNotificacion = $notificacion['link'];

// Obtener el endpoint del destinatario
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

if ($permiso != 1) {
    die(json_encode(["error" => "El destinatario no tiene permisos para recibir notificaciones."]));
}

// Obtener el token de acceso para Firebase
$accessToken = getAccessToken($serviceAccount);
if (!$accessToken) {
    die(json_encode(["error" => "No se pudo obtener el token de acceso."]));
}

// Preparar el mensaje para enviar
$mensaje = [
    "message" => [
        "token" => $endpoint,
        "notification" => [
            "title" => $tituloNotificacion,
            "body" => $mensajeNotificacion,
        ],
        "data" => [
            "click_action" => $linkNotificacion
        ],
        "webpush" => [
            "fcm_options" => [
                "link" => $linkNotificacion
            ]
        ]
    ]
];

// Enviar la notificación
$response = sendRequest($fcmUrl, $accessToken, $mensaje);
echo json_encode(["mensaje_enviado" => true, "respuesta" => json_decode($response, true)]);

// Funciones auxiliares
function getAccessToken($serviceAccount) {
    $url = 'https://oauth2.googleapis.com/token';
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    $jwt = generateJWT($serviceAccount);
    
    file_put_contents('jwt_debug.txt', $jwt); // Guardar el JWT para revisión

    $data = [
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        die(json_encode(["error" => "Error cURL: " . curl_error($ch)]));
    }

    curl_close($ch);
    $responses = json_decode($response, true);
    
    if (!$responses || !isset($responses['access_token'])) {
        die(json_encode(["error" => "Fallo al obtener token", "response" => $responses]));
    }
    
    return $responses['access_token'];
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

    $jwtHeader = base64UrlEncode(json_encode($header));
    $jwtClaim = base64UrlEncode(json_encode($claim));
    $data = "$jwtHeader.$jwtClaim";
    
    // Depurar los datos antes de firmar
    file_put_contents('jwt_data.txt', $data);

    // Cargar la clave privada
    $privateKey = $serviceAccount['private_key'];
    $key = openssl_pkey_get_private($privateKey);
    if ($key === false) {
        die(json_encode(["error" => "Clave privada inválida en generateJWT", "details" => openssl_error_string()]));
    }

    // Obtener la clave pública para verificación
    $publicKeyDetails = openssl_pkey_get_details($key);
    if ($publicKeyDetails === false) {
        die(json_encode(["error" => "No se pudo obtener la clave pública", "details" => openssl_error_string()]));
    }
    $publicKey = $publicKeyDetails['key'];
    file_put_contents('public_key.txt', $publicKey); // Guardar la clave pública

    // Generar la firma
    $signature = '';
    if (!openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256)) {
        die(json_encode(["error" => "Error al generar la firma: " . openssl_error_string()]));
    }

    $jwt = "$data." . base64UrlEncode($signature);

    // Verificar la firma localmente
    $signatureDecoded = base64_decode(strtr($jwtClaim . '.' . $signature, '-_', '+/')); // Reconstruir la firma en base64 estándar
    $isValid = openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($isValid === 1) {
        file_put_contents('signature_verify.txt', "Firma válida localmente");
    } elseif ($isValid === 0) {
        die(json_encode(["error" => "Firma inválida localmente"]));
    } else {
        die(json_encode(["error" => "Error al verificar la firma: " . openssl_error_string()]));
    }

    return $jwt;
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
    
    if (curl_errno($ch)) {
        die(json_encode(["error" => "Error cURL en sendRequest: " . curl_error($ch)]));
    }
    
    curl_close($ch);
    return $response;
}