<?php
require_once 'models/User.php';

class AuthController
{

    private $userModel;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->userModel = new User($db);
    }

    // =========================================================
    // LOGIN: EL NÚCLEO DE ACCESO
    // =========================================================
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // 1. Buscar usuario
            $user = $this->userModel->getUserByEmail($email);

            // 2. Verificar contraseña
            if ($user && password_verify($password, $user['password'])) {
                // Iniciar sesión
                if (session_status() === PHP_SESSION_NONE)
                    session_start();

                // Prevenir Session Fixation (Hijacking)
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nombre_usuario'] = $user['nombre'];
                $_SESSION['rol_nombre'] = $user['rol_nombre'];
                $_SESSION['rol_id'] = $user['rol_id'];

                // --- ASIGNACIÓN DE PERMISOS GRANULARES ---
                // Ahora leemos directamente desde la base de datos (tabla roles),
                // que fueron cargados en $user gracias al JOIN en User.php.
                $_SESSION['p_noticias'] = $user['p_noticias'] ?? 0;
                $_SESSION['p_usuarios'] = $user['p_usuarios'] ?? 0;
                $_SESSION['p_empresa'] = $user['p_empresa'] ?? 0;
                $_SESSION['p_roles'] = $user['p_roles'] ?? 0;
                $_SESSION['p_dashboard_admin'] = $user['p_dashboard_admin'] ?? 0;

                // Mantenemos una bandera general temporal para links que usaban 'isAdmin()' genérico
                // si el proyecto aún lo necesita (por ejemplo para SuperAdmin total override).
                if (strpos(strtoupper($user['rol_nombre']), 'SUPER') !== false) {
                    $_SESSION['is_superadmin'] = true;
                }
                else {
                    $_SESSION['is_superadmin'] = false;
                }

                header("Location: index.php?action=dashboard");
                exit;
            }
            else {
                return "Credenciales incorrectas. Intenta de nuevo.";
            }
        }
        return null;
    }

    // =========================================================
    // REGISTRO PÚBLICO
    // =========================================================
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);

            // Verificar si ya existe
            if ($this->userModel->getUserByEmail($email)) {
                return "Este correo ya está registrado.";
            }

            // Validar contraseñas
            if ($_POST['password'] !== $_POST['confirm_password']) {
                return "Las contraseñas no coinciden.";
            }

            // Crear usuario usando el método corregido (createUser)
            if ($this->userModel->createUser($email, $_POST['password'], $_POST['nombre'], $_POST['apellido'])) {
                header("Location: index.php?action=login&success=Cuenta creada. Inicia sesión.");
                exit;
            }
            else {
                return "Error al registrar en la base de datos.";
            }
        }
        return null;
    }

    // =========================================================
    // CERRAR SESIÓN
    // =========================================================
    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        session_destroy();
        header("Location: index.php?action=login");
        exit;
    }

    // =========================================================
    // RECUPERACIÓN DE CONTRASEÑA (PLACEHOLDERS)
    // =========================================================
    // Mantenemos estos métodos para no romper el enrutador (index.php)

    public function processForgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lógica futura: Enviar correo con token
            return "Funcionalidad de recuperación en mantenimiento (Contacte a TI).";
        }
        return null;
    }

    public function processResetPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lógica futura: Validar token y cambiar password
            return "Funcionalidad de restablecimiento en mantenimiento.";
        }
        return null;
    }
}
?>