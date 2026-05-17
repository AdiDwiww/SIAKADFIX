<?php
class Database {
    private string $host     = 'localhost';
    private string $dbname   = 'uas_psi';
    private string $username = 'root';
    private string $password = '';
    private ?PDO  $conn      = null;

    public function getConnection(): PDO {
        if ($this->conn) return $this->conn;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . $e->getMessage()]);
            exit();
        }
        return $this->conn;
    }
}
