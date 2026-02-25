<?php
require_once 'models/Task.php';

class TaskController
{
    public function index()
    {
        if (empty($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        $db = (new Database())->getConnection();
        $taskModel = new Task($db);
        $tasksList = $taskModel->getAllByUser($_SESSION['user_id']);

        // Cargar usuarios para permitir asignaciones
        require_once 'models/User.php';
        $userModel = new User($db);
        $usersList = $userModel->getAllUsers();

        require_once 'models/Company.php';
        $companyModel = new Company($db);
        $sucursales = $companyModel->getSucursales();
        $cargos = $companyModel->getCargos();

        require 'views/tareas.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['user_id'])) {
            $db = (new Database())->getConnection();
            $taskModel = new Task($db);

            $titulo = $_POST['titulo'] ?? '';
            $descripcion = $_POST['descripcion'] ?? '';
            $prioridad = $_POST['prioridad'] ?? 'media';

            // Format datetime properly if provided
            $asignacion = !empty($_POST['asignacion']) ? $_POST['asignacion'] : ('user_' . $_SESSION['user_id']);
            $curso_link = !empty($_POST['curso_link']) ? $_POST['curso_link'] : null;

            if (!empty($titulo)) {
                require_once 'models/User.php';
                $userModel = new User($db);
                $allUsers = $userModel->getAllUsers();

                $targetUserIds = [];

                if ($asignacion === 'all') {
                    foreach ($allUsers as $u) {
                        $targetUserIds[] = $u['id'];
                    }
                }
                elseif (strpos($asignacion, 'hub_') === 0) {
                    $hubId = str_replace('hub_', '', $asignacion);
                    foreach ($allUsers as $u) {
                        if ($u['sucursal_id'] == $hubId)
                            $targetUserIds[] = $u['id'];
                    }
                }
                elseif (strpos($asignacion, 'area_') === 0) {
                    $areaId = str_replace('area_', '', $asignacion);
                    foreach ($allUsers as $u) {
                        if ($u['cargo_id'] == $areaId)
                            $targetUserIds[] = $u['id'];
                    }
                }
                elseif (strpos($asignacion, 'user_') === 0) {
                    $userId = str_replace('user_', '', $asignacion);
                    $targetUserIds[] = $userId;
                }

                foreach ($targetUserIds as $assigneeId) {
                    $taskModel->create($titulo, $descripcion, $prioridad, $fecha_vencimiento, $assigneeId, $_SESSION['user_id'], $curso_link);
                }
            }
            header("Location: index.php?action=tareas");
            exit;
        }
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            $db = (new Database())->getConnection();
            $taskModel = new Task($db);

            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? null;
            $estado = $input['estado'] ?? null;

            if ($id && $estado) {
                $success = $taskModel->updateStatus($id, $estado, $_SESSION['user_id']);
                echo json_encode(['success' => $success]);
                exit;
            }
            echo json_encode(['success' => false]);
            exit;
        }
    }

    public function delete()
    {
        if (isset($_GET['id']) && !empty($_SESSION['user_id'])) {
            $db = (new Database())->getConnection();
            $taskModel = new Task($db);
            $taskModel->delete($_GET['id'], $_SESSION['user_id']);
            header("Location: index.php?action=tareas");
            exit;
        }
    }
}
?>
