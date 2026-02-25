<?php
// =================================================================================
// VISTA: DIRECTORIO DE COLABORADORES
// =================================================================================
global $globalQuickLinks;
if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}

require_once 'config/database.php';

require_once 'models/User.php';

require_once 'models/QuickLink.php';

$db = (new Database())->getConnection();

$userModel = new User($db);
$usuarios = $userModel->getAllUsers();
$quickLinksList = (new QuickLink($db))->getAll(); // Carga accesos directos por si falla la global

$currentUser = $userModel->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Directorio - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Reutilizamos estilos base para consistencia */
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }
        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
        .ql-admin-add { background: rgba(37,99,235,0.1); color: var(--accent-color); border: 1px dashed var(--accent-color); }
        
        /* ESTILOS ESPECÍFICOS DIRECTORIO */
        .directory-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 20px; }
        .user-card { background: var(--card-bg); border: 1px solid var(--border-subtle); border-radius: 16px; overflow: hidden; transition: 0.3s; display: flex; flex-direction: column; align-items: center; padding: 30px 20px; position: relative; }
        .user-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); border-color: var(--accent-color); }
        
        .uc-avatar { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid var(--card-bg); box-shadow: 0 0 0 2px var(--border-subtle); margin-bottom: 15px; }
        .uc-name { font-size: 16px; font-weight: 800; color: var(--text-main); margin: 0 0 5px 0; text-align: center; }
        .uc-role { font-size: 12px; color: var(--accent-color); font-weight: 700; text-transform: uppercase; margin-bottom: 15px; text-align: center; background: rgba(37,99,235,0.1); padding: 4px 10px; border-radius: 12px; }
        
        .uc-info { width: 100%; margin-top: auto; padding-top: 15px; border-top: 1px dashed var(--border-subtle); font-size: 13px; color: var(--text-secondary); }
        .uc-info-item { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .uc-info-item i { width: 20px; text-align: center; color: var(--text-secondary); opacity: 0.7; }
        
        .search-bar-container { background: var(--card-bg); padding: 20px; border-radius: 16px; border: 1px solid var(--border-subtle); display: flex; gap: 15px; align-items: center; box-shadow: var(--shadow-sm); }
        .search-input-lg { flex: 1; background: var(--input-bg); border: 2px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; color: var(--text-main); outline: none; font-size: 14px; transition: 0.2s; }
        .search-input-lg:focus { border-color: var(--accent-color); background: var(--card-bg); }
        
        .btn-kudo { position: absolute; top: 15px; right: 15px; background: rgba(245, 158, 11, 0.1); color: #F59E0B; border: 1px solid rgba(245, 158, 11, 0.2); width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s; }
        .btn-kudo:hover { transform: scale(1.1); background: #F59E0B; color: white; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); }
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Directorio';
$topbarSearchId = 'dirSearch';
$topbarSearchPlaceholder = 'Buscar por nombre, cargo, sucursal...';
include 'partials/topbar.php';
?>

            <section class="content-area">                <div class="directory-grid" id="dirGrid">
                    <?php foreach ($usuarios as $u):
    // Evitar mostrar al usuario 'admin' genérico si se desea, o usuarios inactivos
    // Preparamos datos de búsqueda
    $searchData = strtolower($u['nombre'] . ' ' . $u['apellido'] . ' ' . $u['cargo_nombre'] . ' ' . $u['sucursal_nombre'] . ' ' . $u['email']);
    $foto = $u['foto_perfil'] ? '<?php echo BASE_URL; ?>assets/uploads/profiles/' . $u['foto_perfil'] : '<?php echo BASE_URL; ?>assets/uploads/profiles/default.png';
?>
                    <div class="user-card" data-search="<?php echo $searchData; ?>">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <button class="btn-kudo" onclick="openKudoModal(<?php echo $u['id']; ?>, '<?php echo addslashes($u['nombre'] . ' ' . $u['apellido']); ?>')" title="Dar Reconocimiento (Kudo)">
                            <i class="fas fa-star"></i>
                        </button>
                        <?php
    endif; ?>
                        
                        <img src="<?php echo $foto; ?>" class="uc-avatar">
                        <h3 class="uc-name"><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></h3>
                        <div class="uc-role"><?php echo htmlspecialchars($u['cargo_nombre'] ?: 'Sin Cargo'); ?></div>
                        
                        <div class="uc-info">
                            <div class="uc-info-item"><i class="fas fa-building"></i> <?php echo htmlspecialchars($u['sucursal_nombre'] ?: 'Oficina Central'); ?></div>
                            <div class="uc-info-item"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($u['email']); ?></div>
                            <div class="uc-info-item"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($u['telefono'] ?: 'No registrado'); ?></div>
                        </div>
                    </div>
                    <?php
endforeach; ?>
                </div>
                
                <div id="noResults" style="display:none; text-align:center; padding:40px; color:var(--text-secondary);">
                    <i class="fas fa-user-slash" style="font-size:40px; opacity:0.5; margin-bottom:10px;"></i>
                    <p>No se encontraron colaboradores.</p>
                </div>
            </section>
        </main>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') body.classList.add('dark-mode');
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light'); });
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        // Buscador en tiempo real
        document.getElementById('dirSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            let visibleCount = 0;
            
            document.querySelectorAll('.user-card').forEach(card => {
                if(card.getAttribute('data-search').includes(term)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
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
    
    <!-- KUDO MODAL -->
    <div id="kudoModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:var(--card-bg); width:90%; max-width:400px; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); overflow:hidden; border:1px solid var(--border-subtle); padding:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="margin:0; font-size:18px; color:var(--text-main);"><i class="fas fa-star" style="color:#F59E0B;"></i> Enviar Kudo</h3>
                <button onclick="document.getElementById('kudoModal').style.display='none'" style="background:none; border:none; color:var(--text-secondary); cursor:pointer;"><i class="fas fa-times"></i></button>
            </div>
            <p style="font-size:13px; color:var(--text-secondary); margin-bottom:15px;">Estás a punto de reconocer el trabajo y actitud de <strong id="kudoTargetName" style="color:var(--text-main);"></strong>. Esto será visible en su perfil.</p>
            
            <input type="hidden" id="kudoTargetId">
            <textarea id="kudoMessage" placeholder="¿Por qué quieres darle este reconocimiento? Ej: Siempre dispuesto a ayudar al equipo..." style="width:100%; min-height:80px; padding:10px; border-radius:10px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-main); outline:none; font-family:'Inter';" required></textarea>
            
            <button onclick="submitKudo()" style="width:100%; padding:12px; background:#F59E0B; color:white; border:none; border-radius:8px; font-weight:700; margin-top:15px; cursor:pointer;">Enviar Kudo</button>
        </div>
    </div>
    
    <!-- SCRIPT GLOBAL NOTIFICACIONES Y BUSQUEDA -->
    <script>
        // Shortcuts
        document.addEventListener("keydown", function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === "k") {
                e.preventDefault(); openUniversalSearch();
            }
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

        function openKudoModal(id, name) {
            document.getElementById('kudoTargetId').value = id;
            document.getElementById('kudoTargetName').innerText = name;
            document.getElementById('kudoMessage').value = '';
            document.getElementById('kudoModal').style.display = 'flex';
        }

        function submitKudo() {
            const id = document.getElementById('kudoTargetId').value;
            const msg = document.getElementById('kudoMessage').value.trim();
            if(!msg) return alert('Por favor escribe un motivo.');

            fetch("index.php?action=ajax_send_kudo", {
                method:"POST", headers:{"Content-Type":"application/x-www-form-urlencoded"}, 
                body:`receptor_id=${id}&motivo=${encodeURIComponent(msg)}`
            }).then(r=>r.json()).then(data => {
                if(data.success) {
                    alert('¡Kudo enviado correctamente!');
                    document.getElementById('kudoModal').style.display='none';
                } else {
                    alert('Error al enviar. Intenta conectarte de nuevo.');
                }
            });
        }

        // Auto-fetch unread counts every minute
        setInterval(fetchNotifications, 60000);
        document.addEventListener("DOMContentLoaded", fetchNotifications);
    </script>
</body>
</html>