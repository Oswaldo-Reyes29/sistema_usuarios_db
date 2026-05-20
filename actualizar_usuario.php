<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

// Solo aceptar método PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Método no permitido. Use PUT o POST.");
}

// Obtener datos
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['id'])) {
    sendResponse(false, "ID de usuario es obligatorio");
}

try {
    $updateFields = [];
    $params = [':id' => $data['id']];
    
    // Construir consulta dinámicamente
    $allowedFields = ['nombre_completo', 'email', 'telefono', 'cargo', 'departamento_id', 'estado'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = :$field";
            $params[":$field"] = $data[$field];
        }
    }
    
    // Si se proporciona nueva contraseña
    if (!empty($data['new_password'])) {
        if (strlen($data['new_password']) < 6) {
            sendResponse(false, "La nueva contraseña debe tener al menos 6 caracteres");
        }
        $hashed_password = password_hash($data['new_password'], PASSWORD_DEFAULT);
        $updateFields[] = "password = :password";
        $params[':password'] = $hashed_password;
    }
    
    if (empty($updateFields)) {
        sendResponse(false, "No hay campos para actualizar");
    }
    
    $query = "UPDATE usuarios_mysql SET " . implode(", ", $updateFields) . " WHERE id = :id";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute($params)) {
        sendResponse(true, "Usuario actualizado correctamente");
    } else {
        sendResponse(false, "Error al actualizar usuario");
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Error en la base de datos: " . $e->getMessage());
}
?>
