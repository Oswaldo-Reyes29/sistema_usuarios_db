<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

// Solo aceptar método GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(false, "Método no permitido. Use GET.");
}

try {
    $query = "SELECT u.*, d.nombre as departamento_nombre 
              FROM usuarios_mysql u
              LEFT JOIN departamentos d ON u.departamento_id = d.id
              ORDER BY u.fecha_creacion DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $usuarios = [];
    while ($row = $stmt->fetch()) {
        // No incluir la contraseña
        unset($row['password']);
        $usuarios[] = $row;
    }
    
    sendResponse(true, "Usuarios cargados correctamente", $usuarios);
    
} catch (PDOException $e) {
    sendResponse(false, "Error al cargar usuarios: " . $e->getMessage());
}
?>
