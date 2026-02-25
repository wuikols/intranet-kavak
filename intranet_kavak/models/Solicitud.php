<?php
class Solicitud
{
    private $conn;
    private $table_name = "solicitudes";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get requests for a specific user
    public function getByUserId($user_id)
    {
        $query = "SELECT s.*, u.nombre, u.apodo, u.foto_perfil 
                  FROM " . $this->table_name . " s
                  JOIN usuarios u ON s.user_id = u.id
                  WHERE s.user_id = :user_id 
                  ORDER BY s.actualizado_en DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all requests (for Admin/RRHH)
    public function getAll()
    {
        $query = "SELECT s.*, u.nombre, u.apodo, u.foto_perfil 
                  FROM " . $this->table_name . " s
                  JOIN usuarios u ON s.user_id = u.id
                  ORDER BY s.actualizado_en DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $query = "SELECT s.*, u.nombre, u.apodo, u.foto_perfil 
                  FROM " . $this->table_name . " s
                  JOIN usuarios u ON s.user_id = u.id
                  WHERE s.id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $categoria, $titulo, $descripcion)
    {
        $query = "INSERT INTO " . $this->table_name . " (user_id, categoria, titulo, descripcion, estado) 
                  VALUES (:user_id, :categoria, :titulo, :descripcion, 'Pendiente')";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':categoria' => $categoria,
            ':titulo' => $titulo,
            ':descripcion' => $descripcion
        ]);
    }

    public function updateStatus($id, $estado, $respuesta_admin = null)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado, respuesta_admin = :respuesta_admin 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':estado' => $estado,
            ':respuesta_admin' => $respuesta_admin,
            ':id' => $id
        ]);
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
