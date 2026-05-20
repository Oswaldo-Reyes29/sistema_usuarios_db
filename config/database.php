<?php
// Cabeceras CORS para Clever Cloud
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Manejar solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Clever Cloud inyecta estas variables automáticamente
        $this->host = getenv('MYSQL_ADDON_HOST');
        $this->db_name = getenv('MYSQL_ADDON_DB');
        $this->username = getenv('MYSQL_ADDON_USER');
        $this->password = getenv('MYSQL_ADDON_PASSWORD');
        $this->port = getenv('MYSQL_ADDON_PORT') ?: '3306';
        
        // Para desarrollo local (XAMPP/WAMP)
        if (empty($this->host)) {
            $this->host = 'bzqiuypvgpeekyhro2mx-mysql.services.clever-cloud.com';
            $this->db_name = 'bzqiuypvgpeekyhro2mx';
            $this->username = 'uulk6nbtywpvi5dn';
            $this->password = 'AGiQxEOKSpASCu438cPz';
            $this->port = '3306';
        }
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . 
                   ";dbname=" . $this->db_name . 
                   ";port=" . $this->port . 
                   ";charset=utf8mb4";
            
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            return $this->conn;
        } catch(PDOException $e) {
            $this->sendResponse(false, "Error de conexión a la base de datos: " . $e->getMessage());
            return null;
        }
    }
    
    public function sendResponse($success, $message, $data = null) {
        $response = ["success" => $success, "message" => $message];
        if ($data !== null) {
            $response["data"] = $data;
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
}

// Función global para respuestas
function sendResponse($success, $message, $data = null) {
    $response = ["success" => $success, "message" => $message];
    if ($data !== null) {
        $response["data"] = $data;
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>
