<?php
require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    $response = [
        "success" => true,
        "message" => "API funcionando correctamente en Clever Cloud",
        "server" => $_SERVER['SERVER_NAME'],
        "php_version" => phpversion(),
        "endpoints" => [
            "departamentos" => "GET /departamentos.php",
            "usuarios" => "POST /usuarios.php",
            "login" => "POST /login.php",
            "obtener_usuarios" => "GET /obtener_usuarios.php",
            "actualizar_usuario" => "PUT /actualizar_usuario.php",
            "eliminar_usuario" => "DELETE /eliminar_usuario.php"
        ]
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Error de conexión a la base de datos"
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>