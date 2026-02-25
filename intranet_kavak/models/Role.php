<?php
class Role
{
    private $conn;
    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function getAll()
    {
        $stmt = $this->conn->prepare("SELECT * FROM roles ORDER BY id ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $p_noticias, $p_usuarios, $p_empresa, $p_roles, $p_dashboard)
    {
        $sql = "INSERT INTO roles (nombre, p_noticias, p_usuarios, p_empresa, p_roles, p_dashboard_admin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $p_noticias ? 1 : 0, $p_usuarios ? 1 : 0, $p_empresa ? 1 : 0, $p_roles ? 1 : 0, $p_dashboard ? 1 : 0]);
    }

    public function update($id, $nombre, $p_noticias, $p_usuarios, $p_empresa, $p_roles, $p_dashboard)
    {
        $sql = "UPDATE roles SET nombre=?, p_noticias=?, p_usuarios=?, p_empresa=?, p_roles=?, p_dashboard_admin=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nombre, $p_noticias ? 1 : 0, $p_usuarios ? 1 : 0, $p_empresa ? 1 : 0, $p_roles ? 1 : 0, $p_dashboard ? 1 : 0, $id]);
    }

    public function delete($id)
    {
        // Prevent deleting Super Admin (assuming ID 1)
        if ($id == 1)
            return false;
        $stmt = $this->conn->prepare("DELETE FROM roles WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>