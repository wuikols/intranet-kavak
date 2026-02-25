<?php
require_once 'config/database.php';
require_once 'models/News.php';

class InteractionController
{
    private $newsModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->newsModel = new News($db);
    }

    public function likePost()
    {
        if (!isset($_SESSION['user_id']) || empty($_POST['news_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or missing data']);
            return;
        }

        $result = $this->newsModel->toggleLike($_SESSION['user_id'], $_POST['news_id']);
        echo json_encode(['success' => true, 'action' => $result['action'], 'likes_count' => $result['count']]);
    }

    public function submitComment()
    {
        if (!isset($_SESSION['user_id']) || empty($_POST['news_id']) || empty($_POST['content'])) {
            echo json_encode(['success' => false, 'message' => 'Data missing']);
            return;
        }

        $newComment = $this->newsModel->addComment($_SESSION['user_id'], $_POST['news_id'], $_POST['content']);

        if ($newComment) {
            // Formatear fecha para devolverla lista
            $newComment['fecha_formateada'] = date('d M Y, H:i', strtotime($newComment['creado_en']));
            // Asegurar foto por defecto
            $newComment['foto_perfil'] = $newComment['foto_perfil'] ?? 'default.png';
            echo json_encode(['success' => true, 'comment' => $newComment]);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'DB Error']);
        }
    }
}