<?php
// =================================================================================
// KAVAK OS - ENRUTADOR PRINCIPAL (INDEX.PHP) - VERSIÓN FINAL CORREGIDA
// =================================================================================

// 1. CONFIGURACIÓN INICIAL
ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CONSTANTE GLOBAL DE RUTAS PARA EVITAR "HARDCODING"
define('BASE_URL', '/intranet_kavak/');

// 2. IMPORTACIÓN DE MODELOS Y CONTROLADORES
require_once 'config/database.php';

require_once 'controllers/AuthController.php';

require_once 'controllers/UserController.php';
require_once 'controllers/TaskController.php';
require_once 'controllers/SolicitudController.php';
require_once 'controllers/InteractionController.php';
require_once 'controllers/GlobalController.php';
require_once 'controllers/WikiController.php';
require_once 'controllers/ForumController.php';
require_once 'controllers/CotizadorController.php';
require_once 'controllers/DypController.php';
require_once 'controllers/NotaPaseController.php';
require_once 'controllers/FormularioPraviaController.php';
require_once 'controllers/TipController.php';


require_once 'models/Company.php';
require_once 'models/Task.php';

require_once 'models/Location.php';

require_once 'models/QuickLink.php';
require_once 'models/Wiki.php';
require_once 'models/Forum.php';
require_once 'models/News.php';

// 3. DATOS GLOBALES
$dbGlobal = (new Database())->getConnection();
$globalQuickLinks = (new QuickLink($dbGlobal))->getAll();
$GLOBALS['quickLinksList'] = $globalQuickLinks;

// 4. ROUTER
$action = $_GET['action'] ?? 'home';
$rutas_publicas = ['login', 'register', 'home', 'forgot_password', 'reset_password', 'process_forgot', 'process_reset'];

if (!isset($_SESSION['user_id']) && !in_array($action, $rutas_publicas)) {
    header("Location: index.php?action=login");
    exit;

}

// Helper para verificar Admin
function isAdmin()
{
    require_once 'models/User.php';
    global $dbGlobal;
    if (empty($_SESSION['user_id']))
        return false;
    $u = (new User($dbGlobal))->getUserById($_SESSION['user_id']);
    $rol_nombre = strtoupper($u['rol_nombre'] ?? '');

    return !empty($_SESSION['is_superadmin']) || !empty($_SESSION['p_empresa']) || !empty($_SESSION['p_usuarios']) || strpos($rol_nombre, 'ADMIN') !== false || strpos($rol_nombre, 'SUPER') !== false || strpos($rol_nombre, 'RRHH') !== false;
}

switch ($action) {

    // --- AUTH ---
    case 'login':
        $auth = new AuthController();
        $error = $auth->login();
        $success = $_GET['success'] ?? null;
        require 'views/login.php';
        break;
    case 'register':
        $auth = new AuthController();
        $error = $auth->register();
        require 'views/register.php';
        break;
    case 'logout':
        $auth = new AuthController();
        $auth->logout();
        break;

    // --- MI ESPACIO ---
    case 'dashboard':
        require 'views/dashboard.php';
        break;
    case 'tareas':
        (new TaskController())->index();
        break;
    case 'create_task':
        (new TaskController())->create();
        break;
    case 'update_task_status':
        (new TaskController())->updateStatus();
        break;
    case 'delete_task':
        (new TaskController())->delete();
        break;
    case 'solicitudes':
        (new SolicitudController())->index();
        break;
    case 'create_solicitud':
        (new SolicitudController())->create();
        break;
    case 'update_solicitud_status':
        (new SolicitudController())->updateStatus();
        break;
    case 'delete_solicitud':
        (new SolicitudController())->delete();
        break;
    case 'profile':
        require 'views/profile.php';
        break;
    case 'update_profile':
        $u = new UserController();
        $error = $u->updateProfile();
        if ($error)
            require 'views/profile.php';
        break;

    // --- DIRECTORIO ---
    case 'directory':
        require 'views/directory.php';
        break;

    // --- WIKI Y PROCESOS ---
    case 'wiki':
        (new WikiController())->index();
        break;

    case 'create_wiki':
        (new WikiController())->create();
        break;

    case 'cotizador':
        (new CotizadorController())->index();
        break;

    case 'ajax_cotizador_search':
        (new CotizadorController())->searchStock();
        break;

    case 'dyp_upgrade':
        (new DypController())->index();
        break;

    case 'nota_pase':
        (new NotaPaseController())->index();
        break;

    case 'formulario_pravia':
        (new FormularioPraviaController())->index();
        break;

    case 'ajax_pravia_submit':
        (new FormularioPraviaController())->submit();
        break;

    case 'ajax_dyp_search':
        (new DypController())->searchVehicle();
        break;

    case 'create_wiki_category':
        (new WikiController())->createCategory();
        break;

    case 'delete_wiki_category':
        (new WikiController())->deleteCategory();
        break;

    case 'delete_wiki_article':
        (new WikiController())->deleteArticle();
        break;

    case 'ajax_wiki_view':
        (new WikiController())->incrementView();
        break;

    // --- FORO ---
    case 'forum':
        (new ForumController())->index();
        break;

    case 'create_topic':
        (new ForumController())->createTopic();
        break;

    case 'create_forum_category':
        (new ForumController())->createCategory();
        break;

    case 'delete_forum_category':
        (new ForumController())->deleteCategory();
        break;

    case 'delete_topic':
        (new ForumController())->deleteTopic();
        break;

    case 'ajax_forum_like':
        (new ForumController())->toggleLike();
        break;

    case 'ajax_forum_comment':
        (new ForumController())->addComment();
        break;

    case 'ajax_forum_view':
        (new ForumController())->incrementView();
        break;

    // --- IA ---
    case 'ajax_ai':
        (new AIController())->processRequest();
        exit;

    // --- LIKES EN NOTICIAS ---
    case 'ajax_like':
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false]);
            exit;
        }
        $db = (new Database())->getConnection();
        $result = (new News($db))->toggleLike($_SESSION['user_id'], $_POST['news_id']);
        echo json_encode(['success' => $result['success'], 'action' => $result['action'] ?? null, 'likes' => $result['likes_count'] ?? 0]);
        exit;

    // --- GLOBALES: BUSQUEDA Y NOTIFICACIONES ---
    case 'ajax_universal_search':
        (new GlobalController())->universalSearch();
        break;
    case 'ajax_get_notifications':
        (new GlobalController())->getNotifications();
        break;
    case 'ajax_read_notifications':
        (new GlobalController())->markRead();
        break;
    case 'ajax_send_kudo':
        (new GlobalController())->sendKudo();
        break;

    // --- ADMINISTRACIÓN ---
    case 'admin_tips':
        (new TipController())->index();
        break;
    case 'create_tip':
        (new TipController())->create();
        break;
    case 'update_tip':
        (new TipController())->update();
        break;
    case 'toggle_tip':
        (new TipController())->toggle();
        break;
    case 'delete_tip':
        (new TipController())->delete();
        break;

    case 'create_quicklink':
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isAdmin()) {
            $db = (new Database())->getConnection();
            (new QuickLink($db))->create($_POST['nombre'], $_POST['url'], $_POST['icono']);
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
        break;
    case 'delete_quicklink':
        if (isset($_GET['id']) && isAdmin()) {
            $db = (new Database())->getConnection();
            (new QuickLink($db))->delete($_GET['id']);
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
        break;

    case 'ajax_get_provincias':
        $db = (new Database())->getConnection();
        echo json_encode((new Location($db))->getProvinciasByRegion($_GET['region_id'] ?? null));
        exit;
    case 'ajax_get_comunas':
        $db = (new Database())->getConnection();
        echo json_encode((new Location($db))->getComunasByRegion($_GET['region_id'] ?? null));
        exit;

    case 'admin_news':
        if (!empty($_SESSION['p_noticias']))
            require 'views/admin_news.php';
        else
            header("Location: index.php");
        break;
    case 'create_news':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { /* ...Logica Noticias... */
            $db = (new Database())->getConnection();
            (new News($db))->create($_POST['titulo'], $_POST['contenido'], $_POST['tipo'], $_POST['fecha_vencimiento'], $_POST['curso_link'] ?? null, null, $_SESSION['user_id']);
            header("Location: index.php?action=admin_news");
        }
        break;
    case 'update_news':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') { /* ...Logica Noticias... */
            $db = (new Database())->getConnection();
            (new News($db))->update($_POST['id'], $_POST['titulo'], $_POST['contenido'], $_POST['tipo'], $_POST['fecha_vencimiento'], $_POST['curso_link'] ?? null, null);
            header("Location: index.php?action=admin_news");
        }
        break;
    case 'delete_news':
        if (isset($_GET['id'])) {
            $db = (new Database())->getConnection();
            (new News($db))->delete($_GET['id']);
            header("Location: index.php?action=admin_news");
        }
        break;

    case 'admin_analytics':
        require_once 'controllers/AnalyticsController.php';
        (new AnalyticsController())->index();
        break;

    case 'admin_users':
        if (!empty($_SESSION['p_usuarios']))
            require 'views/admin_users.php';
        break;
    case 'create_user_admin':
        (new UserController())->createUserAsAdmin();
        break;
    case 'update_user_admin':
        (new UserController())->updateUserAsAdmin();
        break;
    case 'delete_user':
        (new UserController())->delete($_GET['id'] ?? 0);
        break;

    case 'admin_company':
        if (!empty($_SESSION['p_empresa']))
            require 'views/admin_company.php';
        break;
    // ... rutas de empresa (sucursal/cargo) se mantienen ...
    case 'create_sucursal':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db = (new Database())->getConnection();
            (new Company($db))->createSucursal($_POST['nombre'], $_POST['nombre_corto'], $_POST['direccion'], $_POST['comuna_id']);
            header("Location: index.php?action=admin_company");
        }
        break;
    case 'delete_sucursal':
        if (isset($_GET['id'])) {
            $db = (new Database())->getConnection();
            (new Company($db))->deleteSucursal($_GET['id']);
            header("Location: index.php?action=admin_company");
        }
        break;
    case 'create_cargo':
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db = (new Database())->getConnection();
            (new Company($db))->createCargo($_POST['nombre']);
            header("Location: index.php?action=admin_company");
        }
        break;
    case 'delete_cargo':
        if (isset($_GET['id'])) {
            $db = (new Database())->getConnection();
            (new Company($db))->deleteCargo($_GET['id']);
            header("Location: index.php?action=admin_company");
        }
        break;

    // --- ROLES ---
    case 'create_rol':
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['p_empresa'])) {
            require_once 'models/Role.php';
            $db = (new Database())->getConnection();
            (new Role($db))->create(
                $_POST['nombre'],
                isset($_POST['p_noticias']),
                isset($_POST['p_usuarios']),
                isset($_POST['p_empresa']),
                isset($_POST['p_roles']),
                isset($_POST['p_dashboard_admin'])
            );
            header("Location: index.php?action=admin_company&success=" . urlencode("Rol creado correctamente"));
        }
        break;
    case 'update_rol':
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['p_empresa'])) {
            require_once 'models/Role.php';
            $db = (new Database())->getConnection();
            (new Role($db))->update(
                $_POST['id'],
                $_POST['nombre'],
                isset($_POST['p_noticias']),
                isset($_POST['p_usuarios']),
                isset($_POST['p_empresa']),
                isset($_POST['p_roles']),
                isset($_POST['p_dashboard_admin'])
            );
            header("Location: index.php?action=admin_company&success=" . urlencode("Permisos actualizados"));
        }
        break;
    case 'delete_rol':
        if (isset($_GET['id']) && !empty($_SESSION['p_empresa'])) {
            require_once 'models/Role.php';
            $db = (new Database())->getConnection();
            (new Role($db))->delete($_GET['id']);
            header("Location: index.php?action=admin_company&success=" . urlencode("Rol eliminado"));
        }
        break;

    // --- HOME ---
    case 'home':
    default:
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?action=dashboard");
            exit();
        }
        else {
            header("Location: index.php?action=login");
            exit();
        }
        break;
}
?>