<?php
global $globalQuickLinks;
if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}
require_once 'config/database.php';

require_once 'models/User.php';

require_once 'models/News.php';
require_once 'models/QuickLink.php';
require_once 'models/Tip.php';

$db = (new Database())->getConnection();

$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$newsModel = new News($db);
$noticias = $newsModel->getActive($_SESSION['user_id']);
$calendarEvents = $newsModel->getCalendarEvents();

$quickLinksList = (new QuickLink($db))->getAll();
$tipsList = (new Tip($db))->getActive();

$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
$nombre_mostrar = htmlspecialchars($currentUser['apodo'] ?: explode(' ', $currentUser['nombre'])[0]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Inicio - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/intranet_kavak/assets/css/style.css?v=<?php echo time(); ?>">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.5/purify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    
    <style>
        /* Estilos Base Sidebar/Topbar */
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }

        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
        .ql-admin-add { background: rgba(37,99,235,0.1); color: var(--accent-color); border: 1px dashed var(--accent-color); }
        .btn-delete-ql { position: absolute; top: -5px; right: -5px; background: #EF4444; color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 9px; display: none; align-items: center; justify-content: center; border: none; cursor: pointer; }
        .ql-btn:hover .btn-delete-ql { display: flex; }

        /* BANNER */
        .welcome-banner {
            background: linear-gradient(90deg, #0f172a 0%, #1e3a8a 100%);
            border-radius: 24px;
            padding: 40px;
            color: white;
            box-shadow: 0 20px 40px -10px rgba(30, 58, 138, 0.5);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            min-height: 180px;
            cursor: grab;
        }
        .welcome-banner:active { cursor: grabbing; }
        .welcome-text { position: relative; z-index: 10; max-width: 60%; }
        .welcome-text h1 { margin: 0 0 10px 0; font-size: 32px; font-weight: 800; letter-spacing: -1px; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .welcome-text p { margin: 0; font-size: 15px; opacity: 0.9; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.1); padding: 6px 14px; border-radius: 20px; backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.1); }

        .road-effect {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: 
                repeating-linear-gradient(90deg, transparent 0, transparent 50px, rgba(255,255,255,0.03) 50px, rgba(255,255,255,0.03) 52px),
                linear-gradient(to bottom, transparent 60%, rgba(255,255,255,0.05) 60%, rgba(255,255,255,0.05) 62%, transparent 62%);
            z-index: 1;
            animation: roadMove 2s linear infinite;
        }
        .car-container { position: absolute; bottom: 10px; right: 5%; width: 320px; height: 140px; z-index: 5; animation: carSuspension 1s ease-in-out infinite alternate; }
        .car-image { width: 100%; height: 100%; background-image: url('https://cdn-icons-png.flaticon.com/512/3202/3202926.png'); background-repeat: no-repeat; background-position: center; background-size: contain; filter: invert(1) drop-shadow(0 0 15px rgba(59, 130, 246, 0.8)); opacity: 0.9; }
        .speed-trail { position: absolute; top: 50%; right: 280px; width: 100px; height: 2px; background: linear-gradient(to left, rgba(255,255,255,0.8), transparent); border-radius: 2px; animation: speedTrail 0.8s linear infinite; }
        .speed-trail:nth-child(2) { top: 60%; width: 150px; animation-delay: 0.2s; }
        .speed-trail:nth-child(3) { top: 40%; width: 80px; animation-delay: 0.4s; }
        @keyframes roadMove { from { background-position: 0 0; } to { background-position: -100px 0; } }
        @keyframes carSuspension { from { transform: translateY(0); } to { transform: translateY(-3px); } }
        @keyframes speedTrail { from { transform: translateX(0); opacity: 1; } to { transform: translateX(-200px); opacity: 0; } }

        /* TABS */
        .tabs-container { margin-bottom: 25px; border-bottom: 1px solid var(--border-subtle); display: flex; gap: 20px; }
        .tab-btn { background: transparent; border: none; padding: 10px 5px; font-size: 14px; font-weight: 600; color: var(--text-secondary); cursor: pointer; position: relative; transition: 0.3s; }
        .tab-btn:hover { color: var(--text-main); }
        .tab-btn.active { color: var(--accent-color); font-weight: 800; }
        .tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background: var(--accent-color); border-radius: 3px 3px 0 0; }

        /* PAGINACIÓN */
        .pagination-container { display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 30px; }
        .page-btn { background: var(--input-bg); border: 1px solid var(--border-subtle); padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 700; color: var(--text-secondary); transition: 0.2s; }
        .page-btn:hover:not(:disabled) { background: var(--accent-color); color: white; border-color: var(--accent-color); }
        .page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .page-info { font-size: 13px; font-weight: 600; color: var(--text-secondary); }

        /* CALENDARIO MEJORADO */
        .calendar-widget { background: var(--card-bg); border-radius: 24px; border: 1px solid var(--border-subtle); overflow: hidden; box-shadow: var(--shadow-sm); }
        .calendar-header { background: var(--input-bg); padding: 15px 25px; border-bottom: 1px solid var(--border-subtle); display: flex; align-items: center; gap: 10px; }
        .calendar-header i { color: var(--accent-color); font-size: 18px; }
        .calendar-header h3 { margin: 0; font-size: 14px; font-weight: 800; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px; }
        
        .fc { font-family: 'Inter', sans-serif; padding: 20px;}
        .fc-toolbar-title { font-size: 15px !important; font-weight: 800 !important; color: var(--text-main) !important; text-transform: capitalize; }
        .fc-button-primary { background: var(--input-bg) !important; border: 1px solid var(--border-subtle) !important; color: var(--text-secondary) !important; text-transform: capitalize; padding: 5px 12px !important; font-size: 12px !important; border-radius: 8px !important; font-weight: 700 !important; transition: 0.2s; box-shadow: none !important; }
        .fc-button-primary:hover { background: var(--border-subtle) !important; color: var(--text-main) !important; border-color: var(--accent-color) !important; }
        .fc-button-active { background: var(--accent-color) !important; color: white !important; border-color: var(--accent-color) !important; }
        .fc-theme-standard th { border: none !important; padding: 10px 0 !important; }
        .fc-theme-standard td, .fc-theme-standard th { border: 1px solid var(--input-bg) !important; }
        .fc-scrollgrid { border: none !important; }
        .fc-col-header-cell-cushion { color: var(--text-secondary) !important; font-weight: 700 !important; font-size: 11px !important; text-transform: uppercase; }
        .fc-daygrid-day-number { color: var(--text-main); font-size: 13px !important; font-weight: 700; padding: 4px; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; margin: 4px auto !important; transition:0.2s; }
        .fc-daygrid-day-number:hover { background: var(--input-bg) !important; color:var(--accent-color) !important; text-decoration:none !important; }
        .fc-day-today { background: transparent !important; }
        .fc-day-today .fc-daygrid-day-number { background: var(--accent-color) !important; color: white !important; box-shadow: 0 4px 10px rgba(37,99,235,0.3); }
        .fc-event { background: rgba(37,99,235,0.1) !important; border: 1px solid rgba(37,99,235,0.2) !important; color: #1E3A8A !important; border-radius: 6px !important; font-size: 10px !important; font-weight: 700 !important; padding: 3px 6px !important; margin: 2px !important; cursor: pointer; transition:0.2s; }
        .fc-event:hover { background: #1E3A8A !important; color:white !important; transform: scale(1.02); }
        .fc-view-harness { min-height: 200px !important; }
        .fc-scroller { overflow-y: hidden !important; }
        .fc-daygrid-day-frame { min-height: 40px !important; }
        .fc-daygrid-day-events { margin-bottom: 0 !important; }

        .news-card { background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-subtle); overflow: hidden; box-shadow: var(--shadow-sm); transition: 0.3s; display: flex; flex-direction: column; height: 100%; animation: fadeIn 0.5s ease; cursor:pointer;}
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .news-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: var(--accent-color); }
        .news-image-header { width: 100%; height: 140px; object-fit: cover; border-bottom: 1px solid var(--border-subtle); }
        .news-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .news-meta { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-type { padding: 4px 8px; border-radius: 6px; }
        .type-novedad { background: rgba(37,99,235,0.1); color: #2563EB; }
        .type-evento { background: rgba(124,58,237,0.1); color: #7C3AED; }
        .type-curso { background: rgba(217,119,6,0.1); color: #D97706; }
        .news-title { font-size: 15px; font-weight: 800; color: var(--text-main); margin: 0 0 10px 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
        .news-excerpt { font-size: 12px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .news-footer { margin-top: auto; padding-top: 15px; border-top: 1px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center; }
        .like-btn { background: none; border: none; cursor: pointer; color: var(--text-secondary); font-size: 13px; display: flex; align-items: center; gap: 5px; transition: 0.2s; font-weight: 600;}
        .like-btn:hover, .like-btn.liked { color: #EF4444; }

        /* DRAGGABLE WIDGETS */
        .widget-title { cursor: grab; }
        .widget-title:active { cursor: grabbing; }
        .sortable-ghost { opacity: 0.4; }
        .draggable-handle { cursor: grab; }
        .draggable-handle:active { cursor: grabbing; }
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Inicio';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div class="bento-grid">
                    
                    <div class="bento-col-12 welcome-banner">
                        <div class="road-effect"></div>
                        <div class="car-container">
                            <div class="speed-trail"></div>
                            <div class="speed-trail"></div>
                            <div class="speed-trail"></div>
                            <div class="car-image"></div>
                        </div>
                        <div class="welcome-text">
                            <h1>¡Hola de nuevo, <?php echo $nombre_mostrar; ?>! 🚀</h1>
                            <p><i class="fas fa-star" style="color:#FCD34D;"></i> El esfuerzo de hoy es el éxito de mañana.</p>
                        </div>
                    </div>

                    <div class="bento-col-8" style="display:flex; flex-direction:column; gap:25px;">
                        
                        <div class="bento-card" style="align-self: flex-start; margin-bottom: 0; width: 100%;">
                            <div class="widget-title"><i class="fas fa-newspaper" style="color:var(--accent-color);"></i> Últimas Novedades</div>
                            <div class="tabs-container">
                                <button class="tab-btn" onclick="filterNews('Novedad', this)">Novedades</button>
                                <button class="tab-btn" onclick="filterNews('Evento Importante', this)">Eventos</button>
                                <button class="tab-btn" onclick="filterNews('Curso Pendiente', this)">Cursos</button>
                            </div>

                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;" id="newsGrid">
                                <?php if (empty($noticias)): ?>
                                    <div style="grid-column: 1/-1; text-align:center; padding:50px; background:var(--input-bg); border-radius:16px;">
                                        <i class="fas fa-wind" style="font-size:40px; color:var(--text-secondary); opacity:0.5;"></i>
                                        <p style="color:var(--text-secondary);">No hay publicaciones recientes.</p>
                                    </div>
                                <?php
else:
    foreach ($noticias as $n):
        $badgeClass = 'type-novedad';
        if ($n['tipo'] == 'Evento Importante')
            $badgeClass = 'type-evento';
        if ($n['tipo'] == 'Curso Pendiente')
            $badgeClass = 'type-curso';
        $dataCategory = $n['tipo'];
        if (strpos($n['tipo'], 'Novedad') !== false)
            $dataCategory = 'Novedad';
        $searchString = strtolower($n['titulo'] . ' ' . $n['contenido']);
?>
                                <div class="news-card" data-category="<?php echo $dataCategory; ?>" data-search="<?php echo htmlspecialchars($searchString, ENT_QUOTES); ?>" onclick="openNewsModal(<?php echo htmlspecialchars(json_encode($n), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <?php if (!empty($n['curso_imagen'])): ?>
                                        <img src="<?php echo htmlspecialchars($n['curso_imagen']); ?>" class="news-image-header">
                                    <?php
        endif; ?>
                                    <div class="news-body">
                                        <div class="news-meta">
                                            <span class="badge-type <?php echo $badgeClass; ?>"><?php echo $n['tipo']; ?></span>
                                            <span style="color:var(--text-secondary);"><?php echo date('d M', strtotime($n['creado_en'])); ?></span>
                                        </div>
                                        <h3 class="news-title"><?php echo htmlspecialchars($n['titulo']); ?></h3>
                                        <div class="news-excerpt"><?php echo strip_tags($n['contenido']); ?></div>
                                        <div class="news-footer">
                                            <button class="like-btn <?php echo $n['user_liked'] ? 'liked' : ''; ?>" onclick="event.stopPropagation(); toggleLike(this, <?php echo $n['id']; ?>)">
                                                <i class="<?php echo $n['user_liked'] ? 'fas' : 'far'; ?> fa-heart"></i> <?php echo $n['likes_count']; ?>
                                            </button>
                                            <?php if (!empty($n['curso_link'])): ?>
                                                <a href="<?php echo htmlspecialchars($n['curso_link']); ?>" target="_blank" style="font-size:12px; font-weight:700; color:var(--accent-color); text-decoration:none;">Ver <i class="fas fa-arrow-right"></i></a>
                                            <?php
        endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
    endforeach;
endif; ?>
                            </div>

                            <div class="pagination-container" id="paginationControls">
                                <button class="page-btn" id="prevBtn" onclick="changePage(-1)"><i class="fas fa-chevron-left"></i></button>
                                <span class="page-info">Página <span id="currentPage">1</span> de <span id="totalPages">1</span></span>
                                <button class="page-btn" id="nextBtn" onclick="changePage(1)"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>

                        <!-- TIP AND APPLICACIONES GRID -->
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; width: 100%;">
                            <!-- TIP KAVAK DINÁMICO -->
                            <?php if (!empty($tipsList)):
    $randomTip = $tipsList[array_rand($tipsList)];
?>
                            <div class="bento-card" style="padding: 25px; margin-bottom: 0; background: linear-gradient(135deg, var(--corporate-blue), #0F172A); color: white; display: flex; flex-direction: column;">
                                 <div class="widget-title draggable-handle" style="color:rgba(255,255,255,0.8); border-color:rgba(255,255,255,0.1);"><i class="fas fa-lightbulb" style="color:#FCD34D;"></i> Tip de Kavak OS</div>
                                 <h4 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 800; flex-shrink: 0;"><?php echo htmlspecialchars($randomTip['titulo']); ?></h4>
                                 <p style="font-size:14px; line-height:1.6; font-weight:500; opacity:0.9; margin: 0; flex: 1;"><?php echo nl2br(htmlspecialchars($randomTip['contenido'])); ?></p>
                            </div>
                            <?php
endif; ?>

                            <!-- WIDGET APLICACIONES CORPORATIVAS -->
                            <div class="bento-card" style="padding: 25px; display: flex; flex-direction: column; margin-bottom: 0;">
                                <div class="widget-title"><i class="fas fa-th-large" style="color:#3B82F6;"></i> Aplicaciones</div>
                                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:15px; flex: 1;">
                                    <a href="https://slack.com" target="_blank" style="background:var(--input-bg); padding:15px; border-radius:12px; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:var(--text-main); transition:0.2s;" onmouseover="this.style.background='var(--card-bg)'; this.style.boxShadow='var(--shadow-sm)';" onmouseout="this.style.background='var(--input-bg)'; this.style.boxShadow='none';">
                                        <i class="fab fa-slack" style="font-size:24px; color:#E01E5A; margin-bottom:8px;"></i>
                                        <span style="font-size:11px; font-weight:700;">Slack</span>
                                    </a>
                                    <a href="https://salesforce.com" target="_blank" style="background:var(--input-bg); padding:15px; border-radius:12px; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:var(--text-main); transition:0.2s;" onmouseover="this.style.background='var(--card-bg)'; this.style.boxShadow='var(--shadow-sm)';" onmouseout="this.style.background='var(--input-bg)'; this.style.boxShadow='none';">
                                        <i class="fab fa-salesforce" style="font-size:24px; color:#00A1E0; margin-bottom:8px;"></i>
                                        <span style="font-size:11px; font-weight:700;">Salesforce</span>
                                    </a>
                                    <a href="https://drive.google.com" target="_blank" style="background:var(--input-bg); padding:15px; border-radius:12px; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:var(--text-main); transition:0.2s;" onmouseover="this.style.background='var(--card-bg)'; this.style.boxShadow='var(--shadow-sm)';" onmouseout="this.style.background='var(--input-bg)'; this.style.boxShadow='none';">
                                        <i class="fab fa-google-drive" style="font-size:24px; color:#1FA463; margin-bottom:8px;"></i>
                                        <span style="font-size:11px; font-weight:700;">Drive</span>
                                    </a>
                                    <a href="https://mail.google.com" target="_blank" style="background:var(--input-bg); padding:15px; border-radius:12px; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:var(--text-main); transition:0.2s;" onmouseover="this.style.background='var(--card-bg)'; this.style.boxShadow='var(--shadow-sm)';" onmouseout="this.style.background='var(--input-bg)'; this.style.boxShadow='none';">
                                        <i class="fas fa-envelope" style="font-size:24px; color:#EA4335; margin-bottom:8px;"></i>
                                        <span style="font-size:11px; font-weight:700;">Gmail</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="bento-card" style="padding:25px; margin-bottom: 0;">
                            <div class="widget-title" style="display:flex; justify-content:space-between; align-items:center;">
                                <span><i class="fas fa-ticket-alt" style="color:#10B981;"></i> Últimas Solicitudes</span>
                                <button style="background:rgba(16,185,129,0.1); color:#10B981; border:none; width:30px; height:30px; border-radius:8px; cursor:pointer; font-size:14px; transition:0.2s;" onmouseover="this.style.background='#10B981'; this.style.color='white';" onmouseout="this.style.background='rgba(16,185,129,0.1)'; this.style.color='#10B981';" title="Nueva Solicitud"><i class="fas fa-plus"></i></button>
                            </div>
                            
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="padding:15px; border:1px solid var(--border-subtle); border-radius:12px; display:flex; justify-content:space-between; align-items:center; background:var(--input-bg);">
                                    <div style="display:flex; align-items:center; gap:15px;">
                                        <div style="width:40px; height:40px; border-radius:10px; background:rgba(217,119,6,0.1); color:#D97706; display:flex; justify-content:center; align-items:center; font-size:16px;">
                                            <i class="fas fa-laptop-medical"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight:700; font-size:14px; color:var(--text-main);">Renovación de Equipo</div>
                                            <div style="font-size:11px; color:var(--text-secondary); margin-top:2px;">Soporte IT • Solicitado hace 2 días</div>
                                        </div>
                                    </div>
                                    <span style="font-size:10px; font-weight:800; padding:4px 10px; background:rgba(217,119,6,0.1); color:#D97706; border-radius:20px;">EN REVISIÓN</span>
                                </div>

                                <div style="padding:15px; border:1px solid var(--border-subtle); border-radius:12px; display:flex; justify-content:space-between; align-items:center; background:var(--input-bg);">
                                    <div style="display:flex; align-items:center; gap:15px;">
                                        <div style="width:40px; height:40px; border-radius:10px; background:rgba(59,130,246,0.1); color:#3B82F6; display:flex; justify-content:center; align-items:center; font-size:16px;">
                                            <i class="fas fa-plane-departure"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight:700; font-size:14px; color:var(--text-main);">Permiso por Vacaciones</div>
                                            <div style="font-size:11px; color:var(--text-secondary); margin-top:2px;">Recursos Humanos • del 10 al 24 Mar</div>
                                        </div>
                                    </div>
                                    <span style="font-size:10px; font-weight:800; padding:4px 10px; background:rgba(16,185,129,0.1); color:#10B981; border-radius:20px;">APROBADO</span>
                                </div>
                                <div style="padding:15px; border:1px solid var(--border-subtle); border-radius:12px; display:flex; justify-content:space-between; align-items:center; background:var(--input-bg);">
                                    <div style="display:flex; align-items:center; gap:15px;">
                                        <div style="width:40px; height:40px; border-radius:10px; background:rgba(139,92,246,0.1); color:#8B5CF6; display:flex; justify-content:center; align-items:center; font-size:16px;">
                                            <i class="fas fa-file-invoice-dollar"></i>
                                        </div>
                                        <div>
                                            <div style="font-weight:700; font-size:14px; color:var(--text-main);">Reembolso de Peajes</div>
                                            <div style="font-size:11px; color:var(--text-secondary); margin-top:2px;">Finanzas • Mes: Febrero</div>
                                        </div>
                                    </div>
                                    <span style="font-size:10px; font-weight:800; padding:4px 10px; background:var(--input-bg); color:var(--text-secondary); border:1px solid var(--border-subtle); border-radius:20px;">BORRADOR</span>
                                </div>
                            </div>
                            <div style="text-align:center; margin-top:15px;">
                                <a href="#" style="font-size:12px; font-weight:700; color:var(--accent-color);">Ver todo el historial <i class="fas fa-chevron-right" style="font-size:10px;"></i></a>
                            </div>
                        </div>

                    </div>

                    <div class="bento-col-4" id="sortable-sidebar" style="display:flex; flex-direction:column; height: 100%;">
                        <!-- Widget Clima -->
                        <div class="bento-card draggable-handle" style="padding: 20px; margin-bottom: 25px; display:flex; justify-content:space-between; align-items:center; background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); color:white;">
                            <div>
                                <h4 style="margin:0; font-size:11px; font-weight:800; opacity:0.8; text-transform:uppercase; letter-spacing:1px;">Clima Local</h4>
                                <div style="font-size:36px; font-weight:800; margin:5px 0 0 0; letter-spacing:-1px;">24°C</div>
                                <div style="font-size:13px; font-weight:600; opacity:0.9;">Soleado - Santiago</div>
                            </div>
                            <i class="fas fa-sun" style="font-size:50px; opacity:0.9; color:#fef08a; text-shadow: 0 0 20px rgba(253,224,71,0.5);"></i>
                        </div>

                        <!-- Widget Tareas Destacadas -->
                        <div class="bento-card" style="padding:25px; margin-bottom: 25px;">
                            <div class="widget-title"><i class="fas fa-tasks" style="color:#8B5CF6;"></i> Tareas Destacadas</div>
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:var(--input-bg); border-radius:12px; border-left:4px solid #8B5CF6; transition:0.2s;">
                                    <div><div style="font-weight:700; font-size:14px; color:var(--text-main);">Revisar OKRs del Q3</div><div style="font-size:11px; color:var(--text-secondary); margin-top:3px;"><i class="far fa-clock"></i> Vence hoy</div></div>
                                    <input type="checkbox" style="width:20px; height:20px; accent-color:#8B5CF6; cursor:pointer;" onclick="this.parentNode.style.opacity='0.5'">
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:var(--input-bg); border-radius:12px; border-left:4px solid #F59E0B; transition:0.2s;">
                                    <div><div style="font-weight:700; font-size:14px; color:var(--text-main);">Aprobar gastos de viaje</div><div style="font-size:11px; color:var(--text-secondary); margin-top:3px;"><i class="far fa-clock"></i> Vence mañana</div></div>
                                    <input type="checkbox" style="width:20px; height:20px; accent-color:#F59E0B; cursor:pointer;" onclick="this.parentNode.style.opacity='0.5'">
                                </div>
                            </div>
                        </div>

                        <!-- Widget Favoritos -->
                        <div class="bento-card" style="padding: 25px; margin-bottom: 25px;">
                            <div class="widget-title"><i class="fas fa-star" style="color:#F59E0B;"></i> Mis Favoritos</div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:15px;">
                                <a href="#" style="background:var(--input-bg); padding:15px 10px; border-radius:12px; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:var(--text-main); transition:0.2s; border:1px solid transparent;" onmouseover="this.style.borderColor='var(--accent-color)'; this.style.background='var(--card-bg)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='transparent'; this.style.background='var(--input-bg)'; this.style.transform='translateY(0)';">
                                    <i class="fas fa-file-invoice-dollar" style="font-size:24px; color:#10B981; margin-bottom:8px;"></i>
                                    <span style="font-size:11px; font-weight:700;">Mis Recibos</span>
                                </a>
                                <a href="index.php?action=directory" style="background:var(--input-bg); padding:15px 10px; border-radius:12px; display:flex; flex-direction:column; align-items:center; text-decoration:none; color:var(--text-main); transition:0.2s; border:1px solid transparent;" onmouseover="this.style.borderColor='var(--accent-color)'; this.style.background='var(--card-bg)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='transparent'; this.style.background='var(--input-bg)'; this.style.transform='translateY(0)';">
                                    <i class="fas fa-address-book" style="font-size:24px; color:#8B5CF6; margin-bottom:8px;"></i>
                                    <span style="font-size:11px; font-weight:700;">Directorio</span>
                                </a>
                            </div>
                        </div>

                        <!-- Widget Cumpleaños -->
                        <div class="bento-card" style="padding: 25px; margin-bottom: 25px;">
                            <div class="widget-title"><i class="fas fa-birthday-cake" style="color:#ec4899;"></i> Próximos Cumpleaños</div>
                            <div style="display:flex; flex-direction:column; gap:15px; margin-top:15px;">
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:40px; height:40px; border-radius:50%; background:rgba(236,72,153,0.1); display:flex; align-items:center; justify-content:center; color:#ec4899; font-weight:800; font-size:11px; border:1px solid rgba(236,72,153,0.2);">HOY</div>
                                    <div>
                                        <div style="font-weight:700; color:var(--text-main); font-size:14px;">María Pérez</div>
                                        <div style="font-size:11px; color:var(--text-secondary);">Finanzas</div>
                                    </div>
                                    <button style="margin-left:auto; background:#ec4899; border:none; color:white; width:30px; height:30px; border-radius:8px; cursor:pointer; transition:0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'" title="Felicitar"><i class="fas fa-gift"></i></button>
                                </div>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:40px; height:40px; border-radius:50%; background:var(--input-bg); display:flex; align-items:center; justify-content:center; color:var(--text-secondary); font-weight:800; font-size:13px; border:1px solid var(--border-subtle);">12</div>
                                    <div>
                                        <div style="font-weight:700; color:var(--text-main); font-size:14px;">Carlos Ruiz</div>
                                        <div style="font-size:11px; color:var(--text-secondary);">Operaciones</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Calendario mejorado -->
                        <div class="calendar-widget" style="display: flex; flex-direction: column; margin-bottom: 0;">
                            <div class="calendar-header draggable-handle">
                                <i class="fas fa-calendar-alt"></i>
                                <h3>Agenda de Eventos</h3>
                            </div>
                            <div id="calendar" style="padding-bottom: 15px;"></div>
                        </div>
                        

                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAL DE DETALLE DE NOTICIA -->
    <div id="newsModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.85); z-index:6000; justify-content:center; align-items:center; overflow-y:auto; padding:20px 0;">
        <div style="background:var(--card-bg); border-radius:20px; width:90%; max-width:900px; margin:40px auto; box-shadow:var(--shadow-lg); max-height:90vh; overflow-y:auto;">
            <div style="position:relative; display:flex; justify-content:space-between; align-items:center; padding:25px; border-bottom:1px solid var(--border-subtle); flex-shrink:0;">
                <h2 id="modalTitle" style="margin:0; color:var(--text-main); flex:1;"></h2>
                <button onclick="document.getElementById('newsModal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-secondary);">✕</button>
            </div>
            <div style="padding:25px; overflow-y:auto; flex:1;">
                <?php if (!empty($noticias[0]['curso_imagen'])): ?>
                    <img id="modalImage" src="" alt="" style="width:100%; border-radius:12px; margin-bottom:20px; display:none; max-height:300px; object-fit:cover;">
                <?php
endif; ?>
                <div style="display:flex; gap:15px; margin-bottom:20px;">
                    <span id="modalType" style="padding:6px 12px; border-radius:8px; font-size:12px; font-weight:700; text-transform:uppercase;"></span>
                    <span id="modalDate" style="color:var(--text-secondary); font-size:13px; font-weight:600;"></span>
                </div>
                <div id="modalContent" style="color:var(--text-secondary); line-height:1.8; font-size:14px; word-wrap:break-word; overflow-wrap:break-word;"></div>
                <div style="margin-top:25px; padding-top:20px; border-top:1px dashed var(--border-subtle); display:flex; gap:15px; align-items:center; flex-wrap:wrap;">
                    <button id="modalLikeBtn" class="like-btn" onclick="toggleLike(this, currentNewsId)" style="font-size:14px;">
                        <i class="far fa-heart"></i> <span id="modalLikesCount">0</span>
                    </button>
                    <a id="modalCourseLink" href="#" target="_blank" style="font-size:13px; font-weight:700; color:var(--accent-color); text-decoration:none; display:none;">Ver más <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isSuperAdmin): ?>
    <div id="modalAddQuickLink" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.85); z-index:6000; justify-content:center; align-items:center; padding:20px;">
        <div style="background:var(--card-bg); padding:30px; border-radius:20px; width:90%; max-width:500px; box-shadow:var(--shadow-lg);">
            <h3 style="margin-top:0; color:var(--text-main);">Nuevo Acceso</h3>
            <form action="index.php?action=create_quicklink" method="POST">
                <input type="text" name="nombre" placeholder="Nombre" style="width:100%; padding:12px; margin-bottom:15px; border-radius:10px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-main);" required>
                <input type="url" name="url" placeholder="URL" style="width:100%; padding:12px; margin-bottom:15px; border-radius:10px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-main);" required>
                <input type="text" name="icono" placeholder="Icono (fas fa-star)" style="width:100%; padding:12px; margin-bottom:20px; border-radius:10px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-main);" required>
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="document.getElementById('modalAddQuickLink').style.display='none'" style="flex:1; padding:12px; background:var(--input-bg); color:var(--text-main); border:none; border-radius:10px; cursor:pointer;">Cancelar</button>
                    <button type="submit" style="flex:1; padding:12px; background:var(--accent-color); color:white; border:none; border-radius:10px; cursor:pointer;">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    <?php
endif; ?>

    <script>
        const themeToggle = document.getElementById('themeToggle'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') body.classList.add('dark-mode');
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light'); });
        document.getElementById('openSidebar').addEventListener('click', () => { document.querySelector('.sidebar').classList.add('active'); });

        // LOGICA DE FILTRADO Y PAGINACIÓN
        let currentPage = 1;
        const itemsPerPage = 6;
        let activeCategory = 'all';
        let allCards = document.querySelectorAll('.news-card');

        // BUSCADOR DE NOTICIAS (CONECTADO AL TOPBAR)
        const searchInput = document.getElementById('newsSearch');
        if(searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                allCards.forEach(card => {
                    const content = card.getAttribute('data-search');
                    if (content.includes(term)) {
                        card.dataset.matchesSearch = "true";
                    } else {
                        card.dataset.matchesSearch = "false";
                    }
                });
                currentPage = 1;
                renderPagination();
            });
        }

        // Inicializar
        allCards.forEach(card => card.dataset.matchesSearch = "true");

        function filterNews(category, btn) {
            if(btn) {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            }
            activeCategory = category;
            currentPage = 1;
            renderPagination();
        }

        function renderPagination() {
            let visibleCards = [];
            allCards.forEach(card => {
                const cardCat = card.getAttribute('data-category');
                const matchesSearch = card.dataset.matchesSearch === "true";
                
                if ((activeCategory === 'all' || cardCat.includes(activeCategory)) && matchesSearch) {
                    visibleCards.push(card);
                }
                card.style.display = 'none'; 
            });

            const totalPages = Math.ceil(visibleCards.length / itemsPerPage);
            document.getElementById('totalPages').textContent = totalPages || 1;
            document.getElementById('currentPage').textContent = currentPage;

            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            
            visibleCards.slice(start, end).forEach(card => card.style.display = 'flex');

            document.getElementById('prevBtn').disabled = currentPage === 1;
            document.getElementById('nextBtn').disabled = currentPage >= totalPages || totalPages === 0;
        }

        function changePage(delta) {
            currentPage += delta;
            renderPagination();
        }

        renderPagination();

        // Variable global para tracking de noticia actual en modal
        let currentNewsId = null;

        // Función para abrir modal de detalle
        function openNewsModal(newsData) {
            currentNewsId = newsData.id;
            
            // Mapear tipo a clase badge
            let badgeClass = 'type-novedad';
            if(newsData.tipo == 'Evento Importante') badgeClass = 'type-evento';
            if(newsData.tipo == 'Curso Pendiente') badgeClass = 'type-curso';
            
            // Llenar datos del modal
            document.getElementById('modalTitle').textContent = newsData.titulo;
            document.getElementById('modalType').textContent = newsData.tipo;
            document.getElementById('modalType').className = 'badge-type ' + badgeClass;
            document.getElementById('modalDate').textContent = new Date(newsData.creado_en).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('modalContent').innerHTML = DOMPurify.sanitize(newsData.contenido);
            document.getElementById('modalLikesCount').textContent = newsData.likes_count;
            
            // Mostrar imagen si existe
            if(newsData.curso_imagen) {
                document.getElementById('modalImage').src = newsData.curso_imagen;
                document.getElementById('modalImage').style.display = 'block';
            } else {
                document.getElementById('modalImage').style.display = 'none';
            }
            
            // Link del curso si existe
            if(newsData.curso_link) {
                document.getElementById('modalCourseLink').href = newsData.curso_link;
                document.getElementById('modalCourseLink').style.display = 'inline-block';
            } else {
                document.getElementById('modalCourseLink').style.display = 'none';
            }
            
            // Actualizar estado del botón like
            const likeBtn = document.getElementById('modalLikeBtn');
            if(newsData.user_liked) {
                likeBtn.classList.add('liked');
                likeBtn.querySelector('i').className = 'fas fa-heart';
            } else {
                likeBtn.classList.remove('liked');
                likeBtn.querySelector('i').className = 'far fa-heart';
            }
            
            // Mostrar modal
            document.getElementById('newsModal').style.display = 'flex';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('newsModal')?.addEventListener('click', function(e) {
            if(e.target === this) this.style.display = 'none';
        });

        // Calendario
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: { left: 'title', right: 'prev,next' }, 
                contentHeight: 'auto',
                events: <?php echo json_encode($calendarEvents); ?>,
                eventClick: function(info) { alert(info.event.title); }
            });
            calendar.render();
        });

        // Likes
        function toggleLike(btn, newsId) {
            fetch('index.php?action=ajax_like', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'news_id=' + newsId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    btn.innerHTML = `<i class="${data.action === 'liked' ? 'fas' : 'far'} fa-heart"></i> ${data.likes}`;
                    btn.classList.toggle('liked');
                    // Actualizar también en el modal si está abierto
                    const likesCount = document.getElementById('modalLikesCount');
                    if(likesCount && currentNewsId === newsId) {
                        likesCount.textContent = data.likes;
                    }
                }
            });
        }

        // INIT SORTABLE JS (DRAG & DROP WIDGETS)
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof Sortable !== 'undefined') {
                // Nested row (Tareas & Solicitudes)
                new Sortable(document.getElementById('sortable-nested'), {
                    animation: 150,
                    handle: '.widget-title',
                    ghostClass: 'sortable-ghost'
                });

                // Right sidebar (Clima, Favoritos, Cumpleaños, Calendario)
                new Sortable(document.getElementById('sortable-sidebar'), {
                    animation: 150,
                    handle: '.widget-title, .draggable-handle, .calendar-header',
                    ghostClass: 'sortable-ghost'
                });

                // Main Bento Grid (Banner, Grid Tareas, Novedades, Sidebar)
                new Sortable(document.querySelector('.bento-grid'), {
                    animation: 150,
                    handle: '.welcome-banner, .widget-title, .calendar-header, .draggable-handle',
                    ghostClass: 'sortable-ghost'
                });
            }
        });
    </script>

    <!-- UNIVERSAL SEARCH MODAL -->
    <div id="universalSearchModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:9999; align-items:flex-start; justify-content:center; padding-top:10vh;">
        <div style="background:var(--card-bg); width:90%; max-width:650px; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); overflow:hidden; border:1px solid var(--border-subtle);">
            <div style="padding:20px; border-bottom:1px solid var(--border-subtle); display:flex; align-items:center; gap:15px; background:var(--input-bg);">
                <i class="fas fa-search" style="color:var(--accent-color); font-size:20px;"></i>
                <input type="text" id="universalSearchInput" placeholder="Busca empleados, foros, wiki o noticias..." style="flex:1; border:none; background:transparent; font-size:18px; color:var(--text-main); outline:none;">
                <button onclick="document.getElementById('universalSearchModal').style.display='none'" style="background:var(--card-bg); border:1px solid var(--border-subtle); color:var(--text-secondary); font-size:11px; font-weight:800; padding:6px 10px; border-radius:6px; cursor:pointer;">ESC</button>
            </div>
            <div id="universalSearchResults" style="max-height:400px; overflow-y:auto; padding:10px;">
                <div style="padding:30px; text-align:center; color:var(--text-secondary);"><i class="fas fa-keyboard fa-2x" style="margin-bottom:10px; opacity:0.5;"></i><br>Empieza a escribir para buscar globalmente</div>
            </div>
        </div>
    </div>
    
    <!-- SCRIPT GLOBAL NOTIFICACIONES Y BUSQUEDA -->
    <script>
        // Shortcuts
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                document.getElementById("universalSearchModal").style.display="none";
                document.getElementById("notif-dropdown").style.display="none";
            }
        });

        function openUniversalSearch() {
            const modal = document.getElementById("universalSearchModal");
            modal.style.display = "flex";
            setTimeout(() => document.getElementById("universalSearchInput").focus(), 100);
        }

        document.getElementById("universalSearchInput")?.addEventListener("input", function(e) {
            const q = e.target.value;
            if(q.length < 2) return;
            fetch("index.php?action=ajax_universal_search", {
                method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"}, body:"q="+encodeURIComponent(q)
            }).then(r=>r.json()).then(data => {
                const res = document.getElementById("universalSearchResults");
                res.innerHTML = "";
                if(data.results.length === 0) res.innerHTML = "<div style='padding:20px;text-align:center;color:var(--text-secondary);'>No se encontraron resultados.</div>";
                data.results.forEach(it => {
                    let icon = "fa-file"; let url = "#"; let color = "var(--text-secondary)";
                    if(it.type=="wiki"){icon="fa-book"; url="index.php?action=wiki"; color="#3B82F6";}
                    if(it.type=="foro"){icon="fa-comments"; url="index.php?action=forum"; color="#10B981";}
                    if(it.type=="user"){icon="fa-user"; url="index.php?action=directory"; color="#8B5CF6";}
                    if(it.type=="news"){icon="fa-bullhorn"; url="index.php?action=dashboard"; color="#F59E0B";}
                    
                    res.innerHTML += `
                        <a href="${url}" style="display:flex; align-items:center; gap:15px; padding:15px; text-decoration:none; border-radius:10px; transition:0.2s; margin-bottom:5px;" onmouseover="this.style.background='var(--input-bg)'" onmouseout="this.style.background='transparent'">
                            <div style="width:40px; height:40px; border-radius:8px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.05); color:${color};"><i class="fas ${icon}"></i></div>
                            <div>
                                <div style="color:var(--text-main); font-weight:700; font-size:14px; margin-bottom:3px;">${it.title}</div>
                                <div style="color:var(--text-secondary); font-size:12px;">${it.subtitle}</div>
                            </div>
                        </a>`;
                });
            });
        });

        function toggleNotifications() {
            const drop = document.getElementById("notif-dropdown");
            drop.style.display = drop.style.display==="none" ? "block" : "none";
            if(drop.style.display==="block") fetchNotifications();
        }

        function fetchNotifications() {
            fetch("index.php?action=ajax_get_notifications")
            .then(r=>r.json()).then(data=>{
                const badge = document.getElementById("notif-badge");
                const list = document.getElementById("notif-list");
                list.innerHTML = "";
                if(data.unread > 0) { badge.style.display="block"; badge.innerText=data.unread; } else { badge.style.display="none"; }
                
                if(data.items.length === 0) list.innerHTML = "<div style='padding:10px; text-align:center;'>No tienes notificaciones nuevas.</div>";
                data.items.forEach(n => {
                    let st = n.leido==0 ? "font-weight:700; background:rgba(37,99,235,0.05);" : "opacity:0.8;";
                    let icon = "fa-bell"; if(n.tipo=="kudo") icon="fa-star"; if(n.tipo=="foro") icon="fa-comment";
                    list.innerHTML += `
                    <div style="padding:12px; border-bottom:1px solid var(--border-subtle); display:flex; gap:12px; align-items:flex-start; ${st}">
                        <div style="color:var(--accent-color); margin-top:3px;"><i class="fas ${icon}"></i></div>
                        <div>
                            <div style="color:var(--text-main); margin-bottom:4px; font-size:13px;">${n.mensaje}</div>
                            <div style="font-size:11px; color:var(--text-secondary);">${n.fecha_formateada}</div>
                            ${n.enlace ? `<a href="${n.enlace}" style="font-size:11px; font-weight:700; margin-top:5px; display:inline-block; color:var(--accent-color);">Ver <i class="fas fa-arrow-right"></i></a>` : ""}
                        </div>
                    </div>`;
                });
            });
        }
        
        function markAllRead(e) {
            e.preventDefault();
            fetch("index.php?action=ajax_read_notifications", {method:"POST"}).then(()=>fetchNotifications());
        }

        // Auto-fetch unread counts every minute
        setInterval(fetchNotifications, 60000);
        document.addEventListener("DOMContentLoaded", fetchNotifications);
    </script>
</body>
</html>