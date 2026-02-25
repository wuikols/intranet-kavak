<?php
class Notification
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($usuario_id, $tipo, $mensaje, $enlace = null)
    {
        try {
            $sql = "INSERT INTO notificaciones (usuario_id, tipo, mensaje, enlace, leido, creado_en) VALUES (?, ?, ?, ?, 0, NOW())";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$usuario_id, $tipo, $mensaje, $enlace]);
        }
        catch (Exception $e) {
            error_log("Error Notification Create: " . $e->getMessage());
            return false;
        }
    }

    public function getUnreadCount($usuario_id)
    {
        try {
            $sql = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leido = 0";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$usuario_id]);
            return $stmt->fetchColumn();
        }
        catch (Exception $e) {
            return 0;
        }
    }

    public function getNotifications($usuario_id, $limit = 20)
    {
        try {
            $sql = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY creado_en DESC LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $usuario_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            return [];
        }
    }

    public function markAllAsRead($usuario_id)
    {
        try {
            $sql = "UPDATE notificaciones SET leido = 1 WHERE usuario_id = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$usuario_id]);
        }
        catch (Exception $e) {
            return false;
        }
    }
}
?>
