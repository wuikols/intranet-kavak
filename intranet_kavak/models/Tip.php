<?php
class Tip
{
    private $conn;
    private $table_name = "tips";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActive()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE activo = 1 ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($titulo, $contenido)
    {
        $query = "INSERT INTO " . $this->table_name . " (titulo, contenido) VALUES (:titulo, :contenido)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':titulo' => $titulo,
            ':contenido' => $contenido
        ]);
    }

    public function update($id, $titulo, $contenido)
    {
        $query = "UPDATE " . $this->table_name . " SET titulo = :titulo, contenido = :contenido WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':titulo' => $titulo,
            ':contenido' => $contenido,
            ':id' => $id
        ]);
    }

    public function toggleStatus($id)
    {
        $query = "UPDATE " . $this->table_name . " SET activo = NOT activo WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }

    public function delete($id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
