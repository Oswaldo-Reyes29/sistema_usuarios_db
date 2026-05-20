<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) exit();

// Aceptar PUT o POST
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $database->sendResponse(false, "Método no permitido. Use PUT o POST.");
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['id'])) {
    $database->sendResponse(false, "ID de usuario es obligatorio");
}

try {
    $updateFields = [];
    $params = [':id' => $data['id']];
    
    // Campos permitidos para actualizar
    $allowedFields = ['nombre_completo', 'email', 'telefono', 'cargo', 'departamento_id', 'estado'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = :$field";
            $params[":$field"] = $data[$field];
        }
    }
    
    // Actualizar contraseña si se proporciona
    if (!empty($data['new_password'])) {
        if (strlen($data['new_password']) < 6) {
            $database->sendResponse(false, "La nueva contraseña debe tener al menos 6 caracteres");
        }
        $hashed_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $updateFields[] = "password = :password";
        $params[':password'] = $hashed_password;
    }
    
    if (empty($updateFields)) {
        $database->sendResponse(false, "No hay campos para actualizar");
    }
    
    $query = "UPDATE usuarios_mysql SET " . implode(", ", $updateFields) . " WHERE id = :id";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute($params)) {
        $database->sendResponse(true, "Usuario actualizado correctamente");
    } else {
        $database->sendResponse(false, "Error al actualizar usuario");
    }
    
} catch (PDOException $e) {
    $database->sendResponse(false, "Error: " . $e->getMessage());
}
?>