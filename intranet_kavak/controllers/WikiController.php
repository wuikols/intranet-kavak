<?php
require_once 'models/Wiki.php';

class WikiController
{
    public function index()
    {
        require 'views/wiki.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $db = (new Database())->getConnection();
            $ruta_portada = null;
            $ruta_adjunto = null;

            // Subida de archivos (Portada y Adjunto)
            if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] == UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $dir = 'assets/uploads/wiki/covers/';
                    if (!is_dir($dir))
                        mkdir($dir, 0755, true);
                    $path = $dir . 'cover_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['imagen_portada']['tmp_name'], $path))
                        $ruta_portada = BASE_URL . $path;
                }
            }
            if (isset($_FILES['archivo_adjunto']) && $_FILES['archivo_adjunto']['error'] == UPLOAD_ERR_OK) {
                $dir = 'assets/uploads/wiki/files/';
                if (!is_dir($dir))
                    mkdir($dir, 0755, true);
                $safe_name = preg_replace("/[^a-zA-Z0-9.-]/", "_", basename($_FILES['archivo_adjunto']['name']));
                $path = $dir . time() . '_' . $safe_name;
                if (move_uploaded_file($_FILES['archivo_adjunto']['tmp_name'], $path))
                    $ruta_adjunto = BASE_URL . $path;
            }

            $success = (new Wiki($db))->createArticle($_POST['categoria_id'], $_SESSION['user_id'], $_POST['titulo'], $_POST['contenido'], $ruta_portada, $ruta_adjunto);
            if ($success)
                header("Location: index.php?action=wiki&success=Publicado");
            else
                header("Location: index.php?action=wiki&error=Error");
            exit;
        }
    }

    public function createCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
            $db = (new Database())->getConnection();
            (new Wiki($db))->createCategory($_POST['nombre']);
            header("Location: index.php?action=wiki&success=Categoria_creada");
            exit;
        }
    }

    public function deleteCategory()
    {
        if (isset($_GET['id']) && isAdmin()) {
            $db = (new Database())->getConnection();
            if ((new Wiki($db))->deleteCategory($_GET['id'])) {
                header("Location: index.php?action=wiki&success=Categoria_eliminada");
            }
            else {
                header("Location: index.php?action=wiki&error=Error");
            }
            exit;
        }
        header("Location: index.php?action=wiki&error=Acceso_Denegado");
        exit;
    }

    public function deleteArticle()
    {
        if (isset($_GET['id']) && isAdmin()) {
            $db = (new Database())->getConnection();
            if ((new Wiki($db))->deleteArticle($_GET['id'])) {
                header("Location: index.php?action=wiki&success=Articulo_eliminado");
            }
            else {
                header("Location: index.php?action=wiki&error=Error");
            }
            exit;
        }
        header("Location: index.php?action=wiki&error=Acceso_Denegado");
        exit;
    }

    public function incrementView()
    {
        if (isset($_POST['article_id'])) {
            $db = (new Database())->getConnection();
            (new Wiki($db))->incrementViews($_POST['article_id']);
        }
        exit;
    }
}
?>
