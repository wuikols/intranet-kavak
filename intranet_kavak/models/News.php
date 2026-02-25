<?php
class News {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener noticias activas para el Dashboard
    public function getActive($userId) {
        $sql = "SELECT n.*, u.nombre as autor_nombre, u.apellido as autor_apellido, u.foto_perfil,
                (SELECT COUNT(*) FROM likes WHERE noticia_id = n.id) as likes_count,
                (SELECT COUNT(*) FROM likes WHERE noticia_id = n.id AND usuario_id = ?) as user_liked
                FROM noticias n
                JOIN usuarios u ON n.autor_id = u.id
                ORDER BY n.creado_en DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener TODAS las noticias para Admin
    public function getAll() {
        $sql = "SELECT n.*, u.nombre as autor_nombre, u.apellido as autor_apellido 
                FROM noticias n
                JOIN usuarios u ON n.autor_id = u.id
                ORDER BY n.creado_en DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($titulo, $contenido, $tipo, $fecha_vencimiento, $curso_link, $curso_imagen, $autor_id) {
        $sql = "INSERT INTO noticias (titulo, contenido, tipo, fecha_vencimiento, curso_link, curso_imagen, autor_id, creado_en) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $fecha = !empty($fecha_vencimiento) ? $fecha_vencimiento : null;
        return $stmt->execute([$titulo, $contenido, $tipo, $fecha, $curso_link, $curso_imagen, $autor_id]);
    }

    public function update($id, $titulo, $contenido, $tipo, $fecha_vencimiento, $curso_link, $curso_imagen = null) {
        $sql = "UPDATE noticias SET titulo = :titulo, contenido = :contenido, tipo = :tipo, fecha_vencimiento = :fecha, curso_link = :link";
        if ($curso_imagen) { $sql .= ", curso_imagen = :imagen"; }
        $sql .= " WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':titulo', $titulo);
        $stmt->bindValue(':contenido', $contenido);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':fecha', !empty($fecha_vencimiento) ? $fecha_vencimiento : null);
        $stmt->bindValue(':link', $curso_link);
        $stmt->bindValue(':id', $id);
        if ($curso_imagen) { $stmt->bindValue(':imagen', $curso_imagen); }
        return $stmt->execute();
    }

    public function delete($id) {
        $this->conn->prepare("DELETE FROM likes WHERE noticia_id = ?")->execute([$id]);
        $this->conn->prepare("DELETE FROM comentarios WHERE noticia_id = ?")->execute([$id]);
        $stmt = $this->conn->prepare("DELETE FROM noticias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Toggle Like (agregar o remover like)
    public function toggleLike($userId, $newsId) {
        try {
            // Verificar si ya existe like
            $stmt = $this->conn->prepare("SELECT id FROM likes WHERE usuario_id = ? AND noticia_id = ?");
            $stmt->execute([$userId, $newsId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Remover like
                $this->conn->prepare("DELETE FROM likes WHERE usuario_id = ? AND noticia_id = ?")->execute([$userId, $newsId]);
                $action = 'unliked';
            } else {
                // Agregar like
                $this->conn->prepare("INSERT INTO likes (usuario_id, noticia_id) VALUES (?, ?)")->execute([$userId, $newsId]);
                $action = 'liked';
            }

            // Contar likes actuales
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM likes WHERE noticia_id = ?");
            $stmt->execute([$newsId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ['success' => true, 'action' => $action, 'likes_count' => $result['count']];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getComments($newsId) {
        $sql = "SELECT c.*, u.nombre, u.apellido, u.foto_perfil FROM comentarios c JOIN usuarios u ON c.usuario_id = u.id WHERE c.noticia_id = ? ORDER BY c.creado_en ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$newsId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- ACTUALIZACIÓN: FUSIÓN DE EVENTOS Y CUMPLEAÑOS ---
    public function getCalendarEvents() {
        // 1. Obtener Eventos de Noticias
        $sqlNews = "SELECT id, titulo as title, fecha_vencimiento as start, 
                CASE 
                    WHEN tipo = 'Evento Importante' THEN '#7C3AED' 
                    WHEN tipo = 'Curso Pendiente' THEN '#D97706'   
                    ELSE '#2563EB'                                 
                END as color
                FROM noticias 
                WHERE fecha_vencimiento IS NOT NULL";
        $stmt = $this->conn->prepare($sqlNews);
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Obtener Cumpleaños de Usuarios
        $sqlBirth = "SELECT id, nombre, apellido, fecha_nacimiento FROM usuarios WHERE fecha_nacimiento IS NOT NULL";
        $stmtB = $this->conn->prepare($sqlBirth);
        $stmtB->execute();
        $birthdays = $stmtB->fetchAll(PDO::FETCH_ASSOC);

        // 3. Procesar cumpleaños para el año actual
        $currentYear = date('Y');
        foreach($birthdays as $b) {
            // Extraer mes y día
            $date = date('m-d', strtotime($b['fecha_nacimiento']));
            
            // Crear evento para este año
            $events[] = [
                'id' => 'bday_' . $b['id'],
                'title' => '🎂 ' . explode(' ', $b['nombre'])[0] . ' ' . explode(' ', $b['apellido'])[0], // Primer nombre y apellido
                'start' => $currentYear . '-' . $date,
                'color' => '#DB2777', // Color Magenta/Rosa para cumpleaños
                'allDay' => true,
                'classNames' => ['fc-event-birthday'] // Clase CSS extra si la necesitamos
            ];
        }

        return $events;
    }
}
?>