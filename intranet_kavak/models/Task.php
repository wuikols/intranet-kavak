<?php
class Task
{
    private $conn;
    private $table_name = "tareas";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAllByUser($user_id)
    {
        $query = "SELECT t.*, u.nombre as asignado_nombre 
                  FROM " . $this->table_name . " t
                  LEFT JOIN usuarios u ON t.asignado_a = u.id
                  WHERE t.creado_por = :user_id OR t.asignado_a = :user_id
                  ORDER BY t.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($titulo, $descripcion, $prioridad, $fecha_vencimiento, $asignado_a, $creado_por, $curso_link = null)
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  (titulo, descripcion, estado, prioridad, fecha_vencimiento, asignado_a, creado_por, curso_link) 
                  VALUES (:titulo, :descripcion, 'por_hacer', :prioridad, :fecha_vencimiento, :asignado_a, :creado_por, :curso_link)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':prioridad', $prioridad);
        $stmt->bindParam(':fecha_vencimiento', $fecha_vencimiento);
        $stmt->bindParam(':asignado_a', $asignado_a);
        $stmt->bindParam(':creado_por', $creado_por);
        $stmt->bindParam(':curso_link', $curso_link);
        return $stmt->execute();
    }

    public function updateStatus($id, $estado, $user_id)
    {
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id AND (creado_por = :user_id OR asignado_a = :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    public function delete($id, $user_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND creado_por = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
}
?>
