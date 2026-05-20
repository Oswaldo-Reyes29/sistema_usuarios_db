<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

// Solo aceptar método DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendResponse(false, "Método no permitido. Use DELETE.");
}

// Obtener ID de la URL o del cuerpo
$id = null;

// Intentar obtener ID de la URL (REST style)
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    // Intentar obtener del cuerpo JSON
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data && isset($data['id'])) {
        $id = $data['id'];
    }
}

if (!$id) {
    sendResponse(false, "ID de usuario es obligatorio");
}

try {
    $query = "DELETE FROM usuarios_mysql WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            sendResponse(true, "Usuario eliminado correctamente");
        } else {
            sendResponse(false, "Usuario no encontrado");
        }
    } else {
        sendResponse(false, "Error al eliminar usuario");
    }
    
} catch (PDOException $e) {
    sendResponse(false, "Error en la base de datos: " . $e->getMessage());
}
?>
