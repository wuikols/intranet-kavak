<?php
global $globalQuickLinks;

if (empty($_SESSION['p_dashboard_admin'])) {
    die("Acceso denegado. Privilegios insuficientes.");
}

// Data vars ya disponibles via AnalyticsController.php:
// $totalUsers, $newUsers, $totalNews, $totalKudos, $totalForumTopics
// $hubDistribution (array: nombre, cantidad)
// $roleDistribution (array: nombre, cantidad)

$currentUser = (new User((new Database())->getConnection()))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Métricas y Analíticas - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }

        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        
        /* METRICS CARPETA */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .metric-card { background: var(--card-bg); border-radius: 20px; padding: 25px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .metric-icon { position: absolute; right: 20px; top: 25px; font-size: 40px; opacity: 0.1; color: var(--text-main); }
        .metric-title { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 10px; letter-spacing: 1px; }
        .metric-value { font-size: 36px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
        .metric-subtitle { font-size: 12px; color: #10B981; font-weight: 600; display: flex; align-items: center; gap: 5px; }
        
        .metric-card.blue { border-left: 4px solid #3B82F6; }
        .metric-card.blue .metric-icon { color: #3B82F6; opacity: 0.2; }
        
        .metric-card.green { border-left: 4px solid #10B981; }
        .metric-card.green .metric-icon { color: #10B981; opacity: 0.2; }
        
        .metric-card.orange { border-left: 4px solid #F59E0B; }
        .metric-card.orange .metric-icon { color: #F59E0B; opacity: 0.2; }

        .metric-card.purple { border-left: 4px solid #8B5CF6; }
        .metric-card.purple .metric-icon { color: #8B5CF6; opacity: 0.2; }
        
        /* CHARTS SECTION */
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        @media(max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }
        .chart-card { background: var(--card-bg); border-radius: 20px; padding: 25px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); }
        .chart-header { margin-bottom: 20px; }
        .chart-header h3 { font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0; }
        .chart-container { position: relative; height: 300px; width: 100%; display: flex; justify-content: center; align-items: center;}
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Dashboard Analítico';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div style="margin-bottom:20px; color:var(--text-secondary); font-size:14px;">
                    <i class="fas fa-info-circle"></i> Descubre cómo se interactúa dentro de <strong>Kavak OS</strong>. Los datos reflejan la base de datos actual en tiempo real.
                </div>

                <div class="metrics-grid">
                    <div class="metric-card blue">
                        <i class="fas fa-users metric-icon"></i>
                        <div class="metric-title">Usuarios Activos</div>
                        <div class="metric-value"><?php echo $totalUsers; ?></div>
                        <div class="metric-subtitle"><i class="fas fa-arrow-up"></i> <?php echo $newUsers; ?> nuevos este mes</div>
                    </div>
                    
                    <div class="metric-card orange">
                        <i class="fas fa-bullhorn metric-icon"></i>
                        <div class="metric-title">Comunicaciones Globales</div>
                        <div class="metric-value"><?php echo $totalNews; ?></div>
                        <div class="metric-subtitle" style="color:var(--text-secondary);"><i class="fas fa-newspaper"></i> Tablón y Eventos</div>
                    </div>
                    
                    <div class="metric-card green">
                        <i class="fas fa-star metric-icon"></i>
                        <div class="metric-title">Kudos Entregados</div>
                        <div class="metric-value"><?php echo $totalKudos; ?></div>
                        <div class="metric-subtitle" style="color:var(--text-secondary);"><i class="fas fa-handshake"></i> Interacciones POSITIVAS</div>
                    </div>

                    <div class="metric-card purple">
                        <i class="fas fa-comments metric-icon"></i>
                        <div class="metric-title">Temas en el Foro</div>
                        <div class="metric-value"><?php echo $totalForumTopics; ?></div>
                        <div class="metric-subtitle" style="color:var(--text-secondary);"><i class="fas fa-users-class"></i> Comunidad Viva</div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Distribución de Personal por HUB</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="hubChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Jerarquía y Tipos de Roles</h3>
                        </div>
                        <div class="chart-container">
                            <canvas id="roleChart"></canvas>
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <!-- PREPARACIÓN DE DATOS PARA JS -->
    <?php
$hubLabels = [];
$hubData = [];
foreach ($hubDistribution as $h) {
    if ($h['cantidad'] > 0) {
        $hubLabels[] = $h['nombre'];
        $hubData[] = $h['cantidad'];
    }
}

$roleLabels = [];
$roleData = [];
foreach ($roleDistribution as $r) {
    if ($r['cantidad'] > 0) {
        $roleLabels[] = $r['nombre'];
        $roleData[] = $r['cantidad'];
    }
}
?>

    <script>
        const themeToggle = document.getElementById('themeToggle'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') body.classList.add('dark-mode');
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light'); updateChartsTheme(); });
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        // --- CHART JS CONFIGURATION ---
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = body.classList.contains('dark-mode') ? '#94A3B8' : '#64748B';

        const chartColors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#EAB308', '#6366F1'];

        // HUB Chart (Barra Horizontal)
        const ctxHub = document.getElementById('hubChart').getContext('2d');
        const hubChart = new Chart(ctxHub, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($hubLabels); ?>,
                datasets: [{
                    label: 'Empleados',
                    data: <?php echo json_encode($hubData); ?>,
                    backgroundColor: chartColors[0],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { grid: { color: 'rgba(100,100,100,0.1)' } },
                    y: { grid: { display: false } }
                }
            }
        });

        // ROLE Chart (Donut)
        const ctxRole = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(ctxRole, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($roleLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($roleData); ?>,
                    backgroundColor: chartColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });

        function updateChartsTheme() {
            const color = body.classList.contains('dark-mode') ? '#94A3B8' : '#64748B';
            Chart.defaults.color = color;
            hubChart.options.scales.x.grid.color = body.classList.contains('dark-mode') ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
            hubChart.update();
            roleChart.update();
        }
    </script>
</body>
</html>
