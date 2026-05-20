<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

class Database {
    // ESTOS DATOS SE INYECTAN AUTOMATICAMENTE EN CLEVER CLOUD
    private $host = getenv('MYSQL_ADDON_HOST');
    private $db_name = getenv('MYSQL_ADDON_DB');
    private $username = getenv('MYSQL_ADDON_USER');
    private $password = getenv('MYSQL_ADDON_PASSWORD');
    private $port = getenv('MYSQL_ADDON_PORT');
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";port=" . $this->port,
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo json_encode([
                "success" => false, 
                "message" => "Error de conexión: " . $e->getMessage()
            ]);
            exit();
        }
        
        return $this->conn;
    }
}
?>