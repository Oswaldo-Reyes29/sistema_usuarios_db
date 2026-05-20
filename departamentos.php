<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) exit();

// Solo aceptar GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $database->sendResponse(false, "Método no permitido. Use GET.");
}

try {
    $query = "SELECT id, nombre, descripcion FROM departamentos ORDER BY nombre ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $departamentos = [];
    while ($row = $stmt->fetch()) {
        $departamentos[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'descripcion' => $row['descripcion'] ?? ''
        ];
    }
    
    $database->sendResponse(true, "Departamentos cargados correctamente", $departamentos);
    
} catch (PDOException $e) {
    $database->sendResponse(false, "Error al cargar departamentos: " . $e->getMessage());
}
?>