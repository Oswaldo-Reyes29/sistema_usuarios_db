<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) exit();

// Solo aceptar DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    $database->sendResponse(false, "Método no permitido. Use DELETE.");
}

// Obtener ID (desde URL o JSON)
$id = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $data = json_decode(file_get_contents("php://input"), true);
    if ($data && isset($data['id'])) {
        $id = $data['id'];
    }
}

if (!$id) {
    $database->sendResponse(false, "ID de usuario es obligatorio");
}

try {
    $query = "DELETE FROM usuarios_mysql WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            $database->sendResponse(true, "Usuario eliminado correctamente");
        } else {
            $database->sendResponse(false, "Usuario no encontrado");
        }
    } else {
        $database->sendResponse(false, "Error al eliminar usuario");
    }
    
} catch (PDOException $e) {
    $database->sendResponse(false, "Error: " . $e->getMessage());
}
?>