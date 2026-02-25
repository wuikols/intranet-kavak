<?php
class Wiki
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // =========================================================
    // LECTURA DE DATOS
    // =========================================================

    public function getCategories()
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM wiki_categorias ORDER BY nombre ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            // Si la tabla no existe, retornamos array vacío para no romper la vista
            return [];
        }
    }

    public function getAll()
    {
        try {
            $sql = "SELECT w.*, c.nombre as categoria_nombre, u.nombre as autor_nombre, u.apellido as autor_apellido
                    FROM wiki_articulos w 
                    LEFT JOIN wiki_categorias c ON w.categoria_id = c.id 
                    LEFT JOIN usuarios u ON w.autor_id = u.id
                    ORDER BY w.creado_en DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            return [];
        }
    }

    // =========================================================
    // CREACIÓN (CON MODO DIAGNÓSTICO)
    // =========================================================

    public function createArticle($categoria_id, $usuario_id, $titulo, $contenido, $portada, $adjunto)
    {
        try {
            $sql = "INSERT INTO wiki_articulos (categoria_id, autor_id, titulo, contenido, imagen_portada, archivo_adjunto, creado_en) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($sql);

            // Ejecución
            return $stmt->execute([$categoria_id, $usuario_id, $titulo, $contenido, $portada, $adjunto]);
        }
        catch (Exception $e) {
            error_log("Error Base de Datos (Wiki createArticle): " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // GESTIÓN Y UTILIDADES
    // =========================================================

    public function createCategory($nombre)
    {
        if (empty(trim($nombre)))
            return false;
        try {
            $sql = "INSERT INTO wiki_categorias (nombre) VALUES (?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([trim($nombre)]);
        }
        catch (Exception $e) {
            error_log("Error al crear categoría Wiki: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM wiki_categorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function deleteArticle($id)
    {
        try {
            $this->conn->prepare("DELETE FROM notificaciones WHERE tipo = 'wiki' AND enlace LIKE ?")->execute(["%id={$id}%"]);
            $stmt = $this->conn->prepare("DELETE FROM wiki_articulos WHERE id = ?");
            return $stmt->execute([$id]);
        }
        catch (Exception $e) {
            error_log("Error deleting wiki article: " . $e->getMessage());
            return false;
        }
    }

    public function incrementViews($id)
    {
        $stmt = $this->conn->prepare("UPDATE wiki_articulos SET vistas = vistas + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>