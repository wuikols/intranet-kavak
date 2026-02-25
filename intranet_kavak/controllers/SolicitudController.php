<?php
require_once 'models/Solicitud.php';

class SolicitudController
{
    private $solicitudModel;

    public function __construct()
    {
        global $dbGlobal;
        $this->solicitudModel = new Solicitud($dbGlobal);
    }

    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        // Check if user is Admin to see all tickets, or just their own
        if (isAdmin()) {
            $solicitudesList = $this->solicitudModel->getAll();
        }
        else {
            $solicitudesList = $this->solicitudModel->getByUserId($_SESSION['user_id']);
        }

        require 'views/solicitudes.php';
    }

    public function create()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $categoria = $_POST['categoria'] ?? '';
        $titulo = $_POST['titulo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if (!empty($categoria) && !empty($titulo) && !empty($descripcion)) {
            $this->solicitudModel->create($_SESSION['user_id'], $categoria, $titulo, $descripcion);
        }

        header("Location: index.php?action=solicitudes");
        exit;
    }

    public function updateStatus()
    {
        if (!isAdmin()) {
            http_response_code(403);
            exit("Acceso denegado.");
        }

        $id = $_POST['id'] ?? null;
        $estado = $_POST['estado'] ?? '';
        $respuesta_admin = $_POST['respuesta_admin'] ?? null;

        if ($id && !empty($estado)) {
            $this->solicitudModel->updateStatus($id, $estado, $respuesta_admin);
        }

        header("Location: index.php?action=solicitudes");
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
            $this->solicitudModel->delete($id);
        }

        header("Location: index.php?action=solicitudes");
        exit;
    }
}
