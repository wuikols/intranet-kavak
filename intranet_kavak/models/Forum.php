<?php
class Forum
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
            $stmt = $this->conn->prepare("SELECT * FROM foro_categorias ORDER BY nombre ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            return [];
        }
    }

    public function getTopics()
    {
        $userId = $_SESSION['user_id'] ?? 0;

        try {
            // Aseguramos compatibilidad incluso si faltan columnas de likes
            $sql = "SELECT t.*, 
                           c.nombre as categoria_nombre, 
                           u.nombre as autor_nombre, 
                           u.apellido as autor_apellido, 
                           u.foto_perfil,
                           (SELECT COUNT(*) FROM foro_likes WHERE tema_id = t.id) as likes_count,
                           (SELECT COUNT(*) FROM foro_comentarios WHERE tema_id = t.id) as comments_count,
                           (SELECT COUNT(*) FROM foro_likes WHERE tema_id = t.id AND usuario_id = ?) as user_liked
                    FROM foro_temas t
                    LEFT JOIN foro_categorias c ON t.categoria_id = c.id
                    LEFT JOIN usuarios u ON t.autor_id = u.id
                    ORDER BY t.creado_en DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            return [];
        }
    }

    // =========================================================
    // CREACIÓN (CON MODO DIAGNÓSTICO)
    // =========================================================

    public function createTopic($categoria_id, $usuario_id, $titulo, $contenido, $imagen, $is_anon)
    {
        try {
            $sql = "INSERT INTO foro_temas (categoria_id, autor_id, titulo, contenido, imagen, es_anonimo, creado_en) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())";

            $stmt = $this->conn->prepare($sql);

            return $stmt->execute([$categoria_id, $usuario_id, $titulo, $contenido, $imagen, $is_anon]);
        }
        catch (Exception $e) {
            error_log("Error Base de Datos (Foro createTopic): " . $e->getMessage());
            return false;
        }
    }

    public function deleteTopic($id)
    {
        try {
            // Limpieza en cascada manual
            $this->conn->prepare("DELETE FROM foro_likes WHERE tema_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM foro_comentarios WHERE tema_id = ?")->execute([$id]);
            $this->conn->prepare("DELETE FROM notificaciones WHERE tipo = 'foro' AND enlace LIKE ?")->execute(["%id={$id}%"]);
            $stmt = $this->conn->prepare("DELETE FROM foro_temas WHERE id = ?");
            return $stmt->execute([$id]);
        }
        catch (Exception $e) {
            error_log("Error deleting topic: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // GESTIÓN DE CATEGORÍAS
    // =========================================================

    public function createCategory($nombre)
    {
        if (empty(trim($nombre)))
            return false;
        try {
            $sql = "INSERT INTO foro_categorias (nombre) VALUES (?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([trim($nombre)]);
        }
        catch (Exception $e) {
            error_log("Error Categoría Foro: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM foro_categorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================
    // INTERACCIONES
    // =========================================================

    public function toggleLike($userId, $topicId)
    {
        $check = $this->conn->prepare("SELECT id FROM foro_likes WHERE usuario_id = ? AND tema_id = ?");
        $check->execute([$userId, $topicId]);

        if ($check->rowCount() > 0) {
            $del = $this->conn->prepare("DELETE FROM foro_likes WHERE usuario_id = ? AND tema_id = ?");
            $del->execute([$userId, $topicId]);
            $action = 'unliked';
        }
        else {
            $add = $this->conn->prepare("INSERT INTO foro_likes (usuario_id, tema_id) VALUES (?, ?)");
            $add->execute([$userId, $topicId]);
            $action = 'liked';
        }

        $countStmt = $this->conn->prepare("SELECT COUNT(*) FROM foro_likes WHERE tema_id = ?");
        $countStmt->execute([$topicId]);
        return ['action' => $action, 'count' => $countStmt->fetchColumn()];
    }

    public function addComment($topicId, $userId, $content, $isAnon)
    {
        try {
            $sql = "INSERT INTO foro_comentarios (tema_id, usuario_id, contenido, es_anonimo, creado_en) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute([$topicId, $userId, $content, $isAnon])) {
                $userStmt = $this->conn->prepare("SELECT nombre, apellido, foto_perfil FROM usuarios WHERE id = ?");
                $userStmt->execute([$userId]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);
                return array_merge($user, ['contenido' => $content, 'es_anonimo' => $isAnon, 'fecha_formateada' => 'Ahora']);
            }
            return false;
        }
        catch (Exception $e) {
            return false;
        }
    }

    public function incrementViews($id)
    {
        $stmt = $this->conn->prepare("UPDATE foro_temas SET vistas = vistas + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>