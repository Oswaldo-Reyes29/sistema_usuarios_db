<?php
require_once 'database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT id, nombre FROM departamentos ORDER BY nombre ASC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $departamentos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $departamentos[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $departamentos
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
