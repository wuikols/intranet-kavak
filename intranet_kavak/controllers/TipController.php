<?php
require_once 'models/Tip.php';

class TipController
{
    private $tipModel;

    public function __construct()
    {
        global $dbGlobal;
        $this->tipModel = new Tip($dbGlobal);
    }

    public function index()
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit("Acceso denegado.");
        }
        $tips = $this->tipModel->getAll();
        require 'views/admin_tips.php';
    }

    public function create()
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit("Acceso denegado.");
        }
        $titulo = $_POST['titulo'] ?? '';
        $contenido = $_POST['contenido'] ?? '';

        if (!empty($titulo) && !empty($contenido)) {
            $this->tipModel->create($titulo, $contenido);
        }
        header("Location: index.php?action=admin_tips");
        exit;
    }

    public function update()
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit("Acceso denegado.");
        }
        $id = $_POST['id'] ?? null;
        $titulo = $_POST['titulo'] ?? '';
        $contenido = $_POST['contenido'] ?? '';

        if ($id && !empty($titulo) && !empty($contenido)) {
            $this->tipModel->update($id, $titulo, $contenido);
        }
        header("Location: index.php?action=admin_tips");
        exit;
    }

    public function toggle()
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit("Acceso denegado.");
        }
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->tipModel->toggleStatus($id);
        }
        header("Location: index.php?action=admin_tips");
        exit;
    }

    public function delete()
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit("Acceso denegado.");
        }
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->tipModel->delete($id);
        }
        header("Location: index.php?action=admin_tips");
        exit;
    }
}
