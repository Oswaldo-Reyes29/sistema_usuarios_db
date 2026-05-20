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
    $query = "SELECT u.id, u.username, u.nombre_completo, u.email, u.telefono, 
                     u.cargo, u.estado, u.fecha_creacion, d.nombre as departamento_nombre 
              FROM usuarios_mysql u
              LEFT JOIN departamentos d ON u.departamento_id = d.id
              ORDER BY u.fecha_creacion DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $usuarios = [];
    while ($row = $stmt->fetch()) {
        $usuarios[] = $row;
    }
    
    $database->sendResponse(true, "Usuarios cargados correctamente", $usuarios);
    
} catch (PDOException $e) {
    $database->sendResponse(false, "Error al cargar usuarios: " . $e->getMessage());
}
?>