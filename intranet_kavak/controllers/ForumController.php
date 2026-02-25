<?php
require_once 'models/Forum.php';

class ForumController
{
    public function index()
    {
        require 'views/forum.php';
    }

    public function createTopic()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $db = (new Database())->getConnection();
            $ruta_imagen = null;
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $dir = 'assets/uploads/forum/';
                    if (!is_dir($dir))
                        mkdir($dir, 0755, true);
                    $path = $dir . 'topic_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $path))
                        $ruta_imagen = BASE_URL . $path;
                }
            }
            $is_anon = isset($_POST['is_anonymous']) ? 1 : 0;
            $success = (new Forum($db))->createTopic($_POST['categoria_id'], $_SESSION['user_id'], $_POST['titulo'], $_POST['contenido'], $ruta_imagen, $is_anon);
            if ($success)
                header("Location: index.php?action=forum&success=Publicado");
            else
                header("Location: index.php?action=forum&error=Error");
            exit;
        }
    }

    public function createCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
            $db = (new Database())->getConnection();
            (new Forum($db))->createCategory($_POST['nombre']);
            header("Location: index.php?action=forum&success=Categoria_creada");
            exit;
        }
    }

    public function deleteCategory()
    {
        if (isset($_GET['id']) && isAdmin()) {
            $db = (new Database())->getConnection();
            (new Forum($db))->deleteCategory($_GET['id']);
            header("Location: index.php?action=forum&success=Categoria_eliminada");
            exit;
        }
        header("Location: index.php?action=forum&error=Acceso_Denegado");
        exit;
    }

    public function deleteTopic()
    {
        if (isset($_GET['id']) && isAdmin()) {
            $db = (new Database())->getConnection();
            (new Forum($db))->deleteTopic($_GET['id']);
            header("Location: index.php?action=forum&success=Tema_eliminado");
            exit;
        }
        header("Location: index.php?action=forum&error=Acceso_Denegado");
        exit;
    }

    public function toggleLike()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }
        $db = (new Database())->getConnection();
        $result = (new Forum($db))->toggleLike($_SESSION['user_id'], $_POST['tema_id']);
        echo json_encode(['success' => true, 'action' => $result['action'], 'likes_count' => $result['count']]);
        exit;
    }

    public function addComment()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }
        $db = (new Database())->getConnection();
        $is_anon = isset($_POST['is_anonymous']) && $_POST['is_anonymous'] == 'true' ? 1 : 0;
        $comment = (new Forum($db))->addComment($_POST['tema_id'], $_SESSION['user_id'], $_POST['content'], $is_anon);
        echo json_encode(['success' => !!$comment, 'comment' => $comment]);
        exit;
    }

    public function incrementView()
    {
        if (isset($_POST['tema_id'])) {
            $db = (new Database())->getConnection();
            (new Forum($db))->incrementViews($_POST['tema_id']);
        }
        exit;
    }
}
?>
