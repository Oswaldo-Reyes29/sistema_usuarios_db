<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) exit();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $database->sendResponse(false, "Método no permitido. Use POST.");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['username']) || empty($data['password'])) {
    $database->sendResponse(false, "Usuario y contraseña son obligatorios");
}

$username = $data['username'];
$password = $data['password'];

try {
    $query = "SELECT id, username, password, nombre_completo, email, telefono, 
                     cargo, departamento_id, estado, fecha_creacion 
              FROM usuarios_mysql 
              WHERE username = :username";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        // Verificar contraseña
        if (password_verify($password, $usuario['password'])) {
            // Verificar estado (1 = Activo)
            if ($usuario['estado'] != 1) {
                $database->sendResponse(false, "Usuario inactivo. Contacte al administrador.");
            }
            
            // Eliminar contraseña por seguridad
            unset($usuario['password']);
            
            $database->sendResponse(true, "Login exitoso", $usuario);
        } else {
            $database->sendResponse(false, "Contraseña incorrecta");
        }
    } else {
        $database->sendResponse(false, "Usuario no encontrado");
    }
    
} catch (PDOException $e) {
    $database->sendResponse(false, "Error: " . $e->getMessage());
}
?>