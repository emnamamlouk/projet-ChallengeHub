<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private $host = 'sql213.infinityfree.com';
    private $db_name = 'if0_41277969_user';
    private $username = 'if0_41277969';
    private $password = 'PkpgRvhHqhCo';
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die('Erreur de connexion à la base de données.');
        }
    }
    
    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
?>
