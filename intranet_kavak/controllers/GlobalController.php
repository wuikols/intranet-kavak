<?php
require_once 'config/database.php';
require_once 'models/Notification.php';
require_once 'models/Kudo.php';

class GlobalController
{

    public function getNotifications()
    {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['unread' => 0, 'items' => []]);
            exit;
        }

        $db = (new Database())->getConnection();
        $notifModel = new Notification($db);
        $userId = $_SESSION['user_id'];

        $unreadCount = $notifModel->getUnreadCount($userId);
        $rawItems = $notifModel->getNotifications($userId, 10);

        $items = [];
        foreach ($rawItems as $r) {
            $r['fecha_formateada'] = $this->timeElapsedString($r['creado_en']);
            $items[] = $r;
        }

        echo json_encode(['unread' => $unreadCount, 'items' => $items]);
        exit;
    }

    public function markRead()
    {
        if (isset($_SESSION['user_id'])) {
            $db = (new Database())->getConnection();
            (new Notification($db))->markAllAsRead($_SESSION['user_id']);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    public function sendKudo()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id']) || empty($_POST['receptor_id']) || empty($_POST['motivo'])) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos']);
            exit;
        }

        $db = (new Database())->getConnection();
        $emisor = $_SESSION['user_id'];
        $receptor = $_POST['receptor_id'];
        $motivo = trim($_POST['motivo']);
        $insignia = $_POST['insignia'] ?? 'star';

        $kudoCreated = (new Kudo($db))->create($emisor, $receptor, $motivo, $insignia);
        if ($kudoCreated) {
            // Notificar al receptor
            $mensajeNotif = "¡Has recibido un Kudo/Reconocimiento por: " . substr($motivo, 0, 30) . "...!";
            (new Notification($db))->create($receptor, 'kudo', $mensajeNotif, 'index.php?action=profile');
            echo json_encode(['success' => true]);
        }
        else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    public function universalSearch()
    {
        if (!isset($_SESSION['user_id']) || empty($_POST['q'])) {
            echo json_encode(['results' => []]);
            exit;
        }

        $q = trim($_POST['q']);
        if (strlen($q) < 2) {
            echo json_encode(['results' => []]);
            exit;
        }

        $db = (new Database())->getConnection();
        $results = [];

        // BUSCAR EN WIKI
        try {
            $stmt = $db->prepare("SELECT id, titulo, 'wiki' as type FROM wiki_articulos WHERE titulo LIKE ? OR contenido LIKE ? LIMIT 5");
            $likeStr = '%' . $q . '%';
            $stmt->execute([$likeStr, $likeStr]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = ['title' => $row['titulo'], 'subtitle' => 'Artículo en la Wiki', 'type' => 'wiki', 'id' => $row['id']];
            }
        }
        catch (Exception $e) {
        }

        // BUSCAR EN FORO
        try {
            $stmt = $db->prepare("SELECT id, titulo, 'foro' as type FROM foro_temas WHERE titulo LIKE ? OR contenido LIKE ? LIMIT 5");
            $likeStr = '%' . $q . '%';
            $stmt->execute([$likeStr, $likeStr]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = ['title' => $row['titulo'], 'subtitle' => 'Discusión en la Comunidad', 'type' => 'foro', 'id' => $row['id']];
            }
        }
        catch (Exception $e) {
        }

        // BUSCAR USUARIOS
        try {
            $stmt = $db->prepare("SELECT id, nombre, apellido, correo_personal as email, 'user' as type FROM usuarios WHERE nombre LIKE ? OR apellido LIKE ? OR apodo LIKE ? LIMIT 5");
            $likeStr = '%' . $q . '%';
            $stmt->execute([$likeStr, $likeStr, $likeStr]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = ['title' => $row['nombre'] . ' ' . $row['apellido'], 'subtitle' => 'Directorio Kavak', 'type' => 'user', 'id' => $row['id']];
            }
        }
        catch (Exception $e) {
        }

        // BUSCAR NOVEDADES/NOTICIAS
        try {
            $stmt = $db->prepare("SELECT id, titulo, tipo as type FROM noticias WHERE titulo LIKE ? OR contenido LIKE ? LIMIT 5");
            $likeStr = '%' . $q . '%';
            $stmt->execute([$likeStr, $likeStr]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[] = ['title' => $row['titulo'], 'subtitle' => 'Novedad: ' . $row['type'], 'type' => 'news', 'id' => $row['id']];
            }
        }
        catch (Exception $e) {
        }

        echo json_encode(['results' => $results]);
        exit;
    }

    private function timeElapsedString($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'año',
            'm' => 'mes',
            'w' => 'semana',
            'd' => 'día',
            'h' => 'hora',
            'i' => 'minuto',
            's' => 'segundo',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            }
            else {
                unset($string[$k]);
            }
        }

        if (!$full)
            $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' atrás' : 'justo ahora';
    }
}
?>
