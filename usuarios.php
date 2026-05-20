<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) exit();

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $database->sendResponse(false, "Método no permitido. Use POST.");
}

// Obtener datos JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    $database->sendResponse(false, "Datos inválidos o formato incorrecto");
}

// Validar campos requeridos
$required_fields = ['username', 'password', 'nombre_completo'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        $database->sendResponse(false, "El campo '$field' es obligatorio");
    }
}

// Validar longitud
if (strlen($data['username']) < 4) {
    $database->sendResponse(false, "El usuario debe tener al menos 4 caracteres");
}

if (strlen($data['password']) < 6) {
    $database->sendResponse(false, "La contraseña debe tener al menos 6 caracteres");
}

// Validar username (solo letras, números y guión bajo)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
    $database->sendResponse(false, "El usuario solo puede contener letras, números y guión bajo");
}

// Validar email si se proporciona
if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $database->sendResponse(false, "Email inválido");
}

try {
    // Verificar si el usuario ya existe
    $checkStmt = $db->prepare("SELECT id FROM usuarios_mysql WHERE username = :username");
    $checkStmt->execute([':username' => $data['username']]);
    
    if ($checkStmt->fetch()) {
        $database->sendResponse(false, "El nombre de usuario ya existe");
    }
    
    // Encriptar contraseña con BCRYPT
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    // Insertar usuario
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
        $database->sendResponse(true, "Usuario registrado exitosamente", [
            'id' => $db->lastInsertId(),
            'username' => $data['username']
        ]);
    } else {
        $database->sendResponse(false, "Error al registrar usuario");
    }
    
} catch (PDOException $e) {
    $database->sendResponse(false, "Error en la base de datos: " . $e->getMessage());
}
?>
