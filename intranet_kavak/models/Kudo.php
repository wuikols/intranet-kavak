<?php
class Kudo
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($emisor_id, $receptor_id, $motivo, $insignia = 'star')
    {
        try {
            $sql = "INSERT INTO kudos (emisor_id, receptor_id, motivo, insignia, creado_en) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$emisor_id, $receptor_id, $motivo, $insignia]);
        }
        catch (Exception $e) {
            return false;
        }
    }

    public function getByUser($receptor_id)
    {
        try {
            $sql = "SELECT k.*, u.nombre as emisor_nombre, u.apellido as emisor_apellido, u.foto_perfil as emisor_foto 
                    FROM kudos k 
                    JOIN usuarios u ON k.emisor_id = u.id 
                    WHERE k.receptor_id = ? 
                    ORDER BY k.creado_en DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$receptor_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            return [];
        }
    }

    public function getRecentGlobal($limit = 5)
    {
        try {
            $sql = "SELECT k.*, 
                           u1.nombre as emisor_nombre, u1.apellido as emisor_apellido, u1.foto_perfil as emisor_foto,
                           u2.nombre as receptor_nombre, u2.apellido as receptor_apellido, u2.foto_perfil as receptor_foto
                    FROM kudos k 
                    JOIN usuarios u1 ON k.emisor_id = u1.id 
                    JOIN usuarios u2 ON k.receptor_id = u2.id 
                    ORDER BY k.creado_en DESC LIMIT ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            return [];
        }
    }
}
?>
