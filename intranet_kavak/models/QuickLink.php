<?php
class QuickLink {
    private $conn;
    public function __construct($db) { $this->conn = $db; }
    
    public function getAll() {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM accesos_directos ORDER BY orden ASC, id ASC");
            $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }
    
    public function create($nombre, $url, $icono) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO accesos_directos (nombre, url, icono) VALUES (?, ?, ?)");
            return $stmt->execute([trim($nombre), trim($url), trim($icono)]);
        } catch (PDOException $e) { return false; }
    }
    
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM accesos_directos WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) { return false; }
    }
}
?>