<?php
require_once 'models/User.php';

class UserController {

    public function updateProfile() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) { header("Location: index.php?action=login"); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = (new Database())->getConnection();
            $userModel = new User($db);
            $id = $_SESSION['user_id'];
            
            $data = [
                'nombre' => trim($_POST['nombre'] ?? ''),
                'apellido' => trim($_POST['apellido'] ?? ''),
                'apodo' => trim($_POST['apodo'] ?? ''),
                'telefono' => trim($_POST['telefono'] ?? ''),
                'correo_personal' => trim($_POST['correo_personal'] ?? ''),
                'linkedin' => trim($_POST['linkedin'] ?? ''),
                'calle' => trim($_POST['calle'] ?? ''),
                'numeracion' => trim($_POST['numeracion'] ?? ''),
                'depto' => trim($_POST['depto'] ?? ''),
                'region_id' => !empty($_POST['region_id']) ? $_POST['region_id'] : null,
                'provincia_id' => !empty($_POST['provincia_id']) ? $_POST['provincia_id'] : null,
                'comuna_id' => !empty($_POST['comuna_id']) ? $_POST['comuna_id'] : null,
                'emergencia_nombre' => trim($_POST['emergencia_nombre'] ?? ''),
                'emergencia_parentesco' => trim($_POST['emergencia_parentesco'] ?? ''),
                'emergencia_telefono' => trim($_POST['emergencia_telefono'] ?? ''),
                'password' => null,
                'foto_perfil' => null
            ];

            if (empty($data['nombre']) || empty($data['apellido'])) {
                $currentUser = $userModel->getUserById($id);
                $data['nombre'] = $currentUser['nombre'];
                $data['apellido'] = $currentUser['apellido'];
            }

            if (!empty($_POST['new_password'])) {
                if ($_POST['new_password'] !== $_POST['confirm_password']) {
                    header("Location: index.php?action=profile&error=Las contraseñas no coinciden"); exit;
                }
                if (strlen($_POST['new_password']) < 6) {
                    header("Location: index.php?action=profile&error=La contraseña es muy corta"); exit;
                }
                $data['password'] = $_POST['new_password'];
            }

            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['foto_perfil']['tmp_name'];
                $fileName = $_FILES['foto_perfil']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (in_array($fileExtension, ['jpg', 'gif', 'png', 'jpeg', 'webp'])) {
                    $uploadFileDir = 'assets/uploads/profiles/';
                    if (!is_dir($uploadFileDir)) { mkdir($uploadFileDir, 0755, true); }
                    $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                    if(move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                        $data['foto_perfil'] = $newFileName;
                    }
                }
            }

            try {
                if ($userModel->updateProfile($id, $data)) {
                    $_SESSION['nombre_usuario'] = $data['nombre']; 
                    header("Location: index.php?action=profile&success=Perfil actualizado correctamente");
                } else {
                    header("Location: index.php?action=profile&error=No se realizaron cambios");
                }
            } catch (Exception $e) {
                header("Location: index.php?action=profile&error=Error crítico: " . urlencode($e->getMessage()));
            }
            exit;
        }
    }

    // --- MÉTODOS ADMINISTRATIVOS ACTUALIZADOS (INCLUYEN FECHA NACIMIENTO) ---

    public function createUserAsAdmin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = (new Database())->getConnection();
            $userModel = new User($db);
            $defaultPass = substr(preg_replace('/[^0-9]/', '', $_POST['rut']), 0, 4); 
            
            // Capturamos fecha_nacimiento
            $nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;

            if ($userModel->create($_POST['rut'], $_POST['email'], $defaultPass, $_POST['nombre'], $_POST['apellido'], $nacimiento, $_POST['cargo_id'], $_POST['sucursal_id'], $_POST['rol_id'], $_POST['fecha_ingreso'])) {
                header("Location: index.php?action=admin_users&success=Usuario creado correctamente");
            } else {
                header("Location: index.php?action=admin_users&error=Error al crear usuario");
            }
            exit;
        }
    }

    public function updateUserAsAdmin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = (new Database())->getConnection();
            $userModel = new User($db);
            
            // Capturamos fecha_nacimiento
            $nacimiento = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;

            if ($userModel->updateAsAdmin($_POST['id'], $_POST['rut'], $_POST['email'], $_POST['nombre'], $_POST['apellido'], $nacimiento, $_POST['cargo_id'], $_POST['sucursal_id'], $_POST['rol_id'], $_POST['fecha_ingreso'])) {
                header("Location: index.php?action=admin_users&success=Usuario actualizado correctamente");
            } else {
                header("Location: index.php?action=admin_users&error=Error al actualizar");
            }
            exit;
        }
    }

    public function delete($id) {
        if (!$id) { header("Location: index.php?action=admin_users"); exit; }
        $db = (new Database())->getConnection();
        $userModel = new User($db);
        if ($userModel->delete($id)) {
            header("Location: index.php?action=admin_users&success=Usuario eliminado");
        } else {
            header("Location: index.php?action=admin_users&error=Error al eliminar");
        }
        exit;
    }
}
?>