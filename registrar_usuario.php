<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

// Aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Método no permitido. Use POST.");
}

// Obtener datos
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    sendResponse(false, "Datos inválidos");
}

// Validaciones
if (empty($data['username']) || empty($data['password']) || empty($data['nombre_completo'])) {
    sendResponse(false, "Usuario, contraseña y nombre completo son obligatorios");
}

if (strlen($data['username']) < 4) {
    sendResponse(false, "El usuario debe tener al menos 4 caracteres");
}

if (strlen($data['password']) < 6) {
    sendResponse(false, "La contraseña debe tener al menos 6 caracteres");
}

if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
    sendResponse(false, "El usuario solo puede contener letras, números y guión bajo");
}

// Validar email si se proporciona
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, "Email inválido");
}

try {
    // Verificar si el usuario existe
    $checkStmt = $db->prepare("SELECT id FROM usuarios_mysql WHERE username = :username");
    $checkStmt->execute([':username' => $data['username']]);
    
    if ($checkStmt->fetch()) {
        sendResponse(false, "El nombre de usuario ya está registrado");
    }
    
    // Preparar inserción
    $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
    
    $query = "INSERT INTO usuarios_mysql 
              (username, password, nombre_completo, email, telefono, cargo, departamento_id, estado, fecha_creacion) 
              VALUES 
              (:username, :password, :nombre_completo, :email, :telefono, :cargo, :departamento_id, :estado, NOW())";
    
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':username', $data['username']);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':nombre_completo', $data['nombre_completo']);
    $stmt->bindParam(':email', $data['email'] ?? null);
    $stmt->bindParam(':telefono', $data['telefono'] ?? null);
    $stmt->bindParam(':cargo', $data['cargo'] ?? null);
    $stmt->bindParam(':departamento_id', $data['departamento_id'] ?? null);
    $stmt->bindParam(':estado', $data['estado'] ?? 1);
    
    if ($stmt->execute()) {
        sendResponse(true, "Usuario registrado exitosamente", [
            'id' => $db->lastInsertId(),
            'username' => $data['username']
        ]);
    } else {
        sendResponse(false, "Error al registrar usuario");
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Error: " . $e->getMessage());
}
?>