<?php
class MySessionHandler extends SessionHandler
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function read($id)
    {
        $stmt = $this->conn->prepare("SELECT data FROM sessions WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data'] : '';
    }

    public function write($id, $data)
    {
        $stmt = $this->conn->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (:id, :data, NOW())");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':data', $data);
        return $stmt->execute();
    }

    public function destroy($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function gc($maxlifetime)
    {
        $stmt = $this->conn->prepare("DELETE FROM sessions WHERE timestamp < NOW() - INTERVAL :maxlifetime SECOND");
        $stmt->bindParam(':maxlifetime', $maxlifetime, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

// Database connection
include '../database/connection.php';

// Set custom session handler
$handler = new MySessionHandler($conn);
session_set_save_handler($handler, true);

// Start the session
session_start();
?>