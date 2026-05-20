<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if ($data) {
        try {
            $username = $data['username'];
            $password = password_hash($data['password'], PASSWORD_BCRYPT);
            $nombre_completo = $data['nombre_completo'];
            $email = $data['email'];
            $telefono = $data['telefono'];
            $cargo = $data['cargo'];
            $departamento_id = $data['departamento_id'];
            $estado = $data['estado'];
            $fecha_creacion = date('Y-m-d H:i:s');
            
            $query = "INSERT INTO usuarios_mysql 
                      (username, password, nombre_completo, email, telefono, cargo, departamento_id, estado, fecha_creacion) 
                      VALUES 
                      (:username, :password, :nombre_completo, :email, :telefono, :cargo, :departamento_id, :estado, :fecha_creacion)";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':nombre_completo', $nombre_completo);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':cargo', $cargo);
            $stmt->bindParam(':departamento_id', $departamento_id);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':fecha_creacion', $fecha_creacion);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente',
                    'id' => $db->lastInsertId()
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al registrar usuario'
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Datos inválidos'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>