<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Solo aceptar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Método no permitido. Use POST.");
}

// Obtener datos
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['username']) || empty($data['password'])) {
    sendResponse(false, "Usuario y contraseña son obligatorios");
}

$username = $data['username'];
$password = $data['password'];

try {
    $query = "SELECT id, username, password, nombre_completo, email, telefono, cargo, departamento_id, estado 
              FROM usuarios_mysql 
              WHERE username = :username";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        // Verificar contraseña
        if (password_verify($password, $usuario['password'])) {
            // Verificar estado del usuario (1 = Activo)
            if ($usuario['estado'] != 1) {
                sendResponse(false, "Usuario inactivo. Contacte al administrador.");
            }
            
            // Eliminar contraseña antes de enviar
            unset($usuario['password']);
            
            sendResponse(true, "Login exitoso", $usuario);
        } else {
            sendResponse(false, "Contraseña incorrecta");
        }
    } else {
        sendResponse(false, "Usuario no encontrado");
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Error en la base de datos: " . $e->getMessage());
}
?>