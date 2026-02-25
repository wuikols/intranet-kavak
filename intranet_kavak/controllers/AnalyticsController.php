<?php
require_once 'models/User.php';
require_once 'models/Company.php';
require_once 'models/News.php';

class AnalyticsController
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->getConnection();
    }

    public function index()
    {
        if (empty($_SESSION['p_dashboard_admin'])) {
            die("Acceso denegado. Privilegios insuficientes.");
        }

        // --- DASHBOARD METRICS CALCULATION ---

        // 1. Usuarios
        $stmtUsers = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios");
        $stmtUsers->execute();
        $totalUsers = $stmtUsers->fetch(PDO::FETCH_ASSOC)['total'];

        // Usuarios nuevos este mes
        $stmtNewUsers = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE MONTH(fecha_ingreso) = MONTH(CURRENT_DATE()) AND YEAR(fecha_ingreso) = YEAR(CURRENT_DATE())");
        $stmtNewUsers->execute();
        $newUsers = $stmtNewUsers->fetch(PDO::FETCH_ASSOC)['total'];

        // 2. Comunicaciones (Noticias/Eventos)
        $stmtNews = $this->db->prepare("SELECT COUNT(*) as total FROM noticias");
        $stmtNews->execute();
        $totalNews = $stmtNews->fetch(PDO::FETCH_ASSOC)['total'];

        // 3. Reconocimientos (Kudos)
        $stmtKudos = $this->db->prepare("SELECT COUNT(*) as total FROM kudos");
        $stmtKudos->execute();
        $totalKudos = $stmtKudos->fetch(PDO::FETCH_ASSOC)['total'];

        // 4. Actividad en el Foro
        $stmtForum = $this->db->prepare("SELECT COUNT(*) as total FROM foro_temas");
        $stmtForum->execute();
        $totalForumTopics = $stmtForum->fetch(PDO::FETCH_ASSOC)['total'];

        // 5. Gráfico - Distribución por Hub (Sucursal)
        $stmtHubs = $this->db->prepare("SELECT s.nombre, COUNT(u.id) as cantidad FROM sucursales s LEFT JOIN usuarios u ON s.id = u.sucursal_id GROUP BY s.id ORDER BY cantidad DESC");
        $stmtHubs->execute();
        $hubDistribution = $stmtHubs->fetchAll(PDO::FETCH_ASSOC);

        // 6. Gráfico - Distribución de Roles
        $stmtRoles = $this->db->prepare("SELECT r.nombre, COUNT(u.id) as cantidad FROM roles r LEFT JOIN usuarios u ON r.id = u.rol_id GROUP BY r.id ORDER BY cantidad DESC");
        $stmtRoles->execute();
        $roleDistribution = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

        // Pasamos variables a la vista
        require 'views/admin_analytics.php';
    }
}
?>
