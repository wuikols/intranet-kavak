<?php
if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/QuickLink.php';

$db = (new Database())->getConnection();
$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$quickLinksList = (new QuickLink($db))->getAll();

$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isAdmin = isAdmin();

// Count status for the sidebar
$countInbox = count($solicitudesList);
$countProgreso = 0;
$countPendiente = 0;
$countCerrados = 0;

foreach ($solicitudesList as $sol) {
    if ($sol['estado'] == 'En Progreso')
        $countProgreso++;
    if ($sol['estado'] == 'Pendiente')
        $countPendiente++;
    if ($sol['estado'] == 'Resuelto' || $sol['estado'] == 'Cerrado')
        $countCerrados++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Mis Solicitudes - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/intranet_kavak/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .page-header { background: linear-gradient(135deg, #10B981 0%, #047857 100%); color: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.5); margin-bottom: 30px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; }
        .page-header h1 { margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -1px; }
        .page-header p { margin: 10px 0 0 0; font-size: 15px; opacity: 0.9; }
        .ph-icon { font-size: 100px; position: absolute; right: 2%; top: -10px; opacity: 0.1; transform: rotate(15deg); }
        .btn-new-ticket { background: white; color: #10B981; padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 14px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: 0.2s; position: relative; z-index: 10; border: none; cursor:pointer;}
        .btn-new-ticket:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }

        .dashboard-grid { display: grid; grid-template-columns: 250px 1fr; gap: 30px; align-items: start;}
        @media(max-width:900px) { .dashboard-grid { grid-template-columns: 1fr; } }
        
        .sidebar-menu-nav { background: var(--card-bg); border-radius: 20px; padding: 20px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); position:sticky; top: 100px;}
        .nav-link { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border-radius: 10px; color: var(--text-secondary); text-decoration: none; font-weight: 600; font-size: 14px; margin-bottom: 8px; transition: 0.2s; cursor:pointer;}
        .nav-link:hover, .nav-link.active { background: var(--input-bg); color: #10B981; }
        .nav-badge { background: rgba(16, 185, 129, 0.1); color: #10B981; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 800; }

        .tickets-list { display: flex; flex-direction: column; gap: 15px; }
        .ticket-card { background: var(--card-bg); border-radius: 16px; padding: 20px; border: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; transition: 0.3s; cursor: pointer; }
        .ticket-card:hover { border-color: #10B981; transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .ticket-icon { width: 45px; height: 45px; border-radius: 12px; background: var(--input-bg); display: flex; align-items: center; justify-content: center; font-size: 20px; color: var(--text-secondary); margin-right: 20px; flex-shrink:0;}
        .ticket-info { flex: 1; }
        .ticket-id { font-size: 11px; color: var(--text-secondary); font-weight: 800; margin-bottom: 5px; text-transform:uppercase;}
        .ticket-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
        .ticket-desc { font-size: 13px; color: var(--text-secondary); display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .st-en-progreso { background: rgba(59, 130, 246, 0.1); color: #3B82F6; border: 1px solid rgba(59, 130, 246, 0.2); }
        .st-resuelto, .st-cerrado { background: rgba(16, 185, 129, 0.1); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .st-pendiente { background: rgba(245, 158, 11, 0.1); color: #F59E0B; border: 1px solid rgba(245, 158, 11, 0.2); }

    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Panel de Solicitudes';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div class="page-header">
                    <i class="fas fa-ticket-alt ph-icon"></i>
                    <div>
                        <h1>Soporte Interno</h1>
                        <p><?php echo $isAdmin ? 'Administra y resuelve los tickets generados por toda la empresa.' : 'Tramita y haz seguimiento a tus solicitudes corporativas, vacaciones o incidentes IT.'; ?></p>
                    </div>
                    <button class="btn-new-ticket" onclick="document.getElementById('modalCreateSolicitud').style.display='flex'"><i class="fas fa-plus"></i> Crear Solicitud</button>
                </div>

                <div class="dashboard-grid">
                    <div class="sidebar-menu-nav">
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">Filtros de Tickets</div>
                        <a onclick="filterTickets('all')" class="nav-link active" id="f-all"><i class="fas fa-inbox"></i> Todos <span class="nav-badge"><?php echo $countInbox; ?></span></a>
                        <a onclick="filterTickets('Pendiente')" class="nav-link" id="f-pendiente"><i class="fas fa-exclamation-circle"></i> Pendiente Acción <span class="nav-badge" style="background:rgba(245, 158, 11, 0.1); color:#F59E0B;"><?php echo $countPendiente; ?></span></a>
                        <a onclick="filterTickets('En Progreso')" class="nav-link" id="f-progreso"><i class="fas fa-hourglass-half"></i> En Progreso <span class="nav-badge" style="background:rgba(59, 130, 246, 0.1); color:#3B82F6;"><?php echo $countProgreso; ?></span></a>
                        <a onclick="filterTickets('Resuelto')" class="nav-link" id="f-resuelto"><i class="fas fa-check-double"></i> Resueltos / Cerrados <span class="nav-badge" style="background:rgba(107, 114, 128, 0.1); color:#6B7280;"><?php echo $countCerrados; ?></span></a>
                    </div>

                    <div class="tickets-list" id="ticketsContainer">
                        <?php if (empty($solicitudesList)): ?>
                            <div style="text-align:center; padding:50px; background:var(--card-bg); border-radius:20px; border:1px solid var(--border-subtle);">
                                <i class="fas fa-check-circle" style="font-size:40px; color:#10B981; margin-bottom:15px; opacity:0.5;"></i>
                                <h3 style="color:var(--text-main); font-weight:800; font-size:18px;">¡Todo al día!</h3>
                                <p style="color:var(--text-secondary); font-size:14px;">No tienes solicitudes pendientes.</p>
                            </div>
                        <?php
else:
    foreach ($solicitudesList as $sol):
        $dateStr = date('d M, Y H:i', strtotime($sol['actualizado_en']));

        $iconColor = '#6B7280';
        $iconClass = 'fas fa-ticket-alt';
        if ($sol['categoria'] == 'Recursos Humanos' || $sol['categoria'] == 'Vacaciones') {
            $iconColor = '#F59E0B';
            $iconClass = 'fas fa-umbrella-beach';
        }
        elseif ($sol['categoria'] == 'Sistemas (IT)') {
            $iconColor = '#3B82F6';
            $iconClass = 'fas fa-laptop-code';
        }
        elseif ($sol['categoria'] == 'Finanzas') {
            $iconColor = '#10B981';
            $iconClass = 'fas fa-file-invoice-dollar';
        }

        $statusClass = 'st-' . strtolower(str_replace(' ', '-', $sol['estado']));
?>
                        <div class="ticket-card" data-status="<?php echo $sol['estado']; ?>" onclick="openTicketModal(<?php echo htmlspecialchars(json_encode($sol), ENT_QUOTES, 'UTF-8'); ?>)">
                            <div style="display:flex; align-items:center; flex:1;">
                                <div class="ticket-icon" style="color:<?php echo $iconColor; ?>;"><i class="<?php echo $iconClass; ?>"></i></div>
                                <div class="ticket-info">
                                    <div class="ticket-id">REQ-#<?php echo str_pad($sol['id'], 4, '0', STR_PAD_LEFT); ?> • <?php echo htmlspecialchars($sol['categoria']); ?> <?php echo $isAdmin ? " • De: " . htmlspecialchars($sol['nombre']) : ""; ?> • Act. <?php echo $dateStr; ?></div>
                                    <div class="ticket-title"><?php echo htmlspecialchars($sol['titulo']); ?></div>
                                    <div class="ticket-desc"><?php echo htmlspecialchars($sol['descripcion']); ?></div>
                                </div>
                            </div>
                            <div><span class="status-badge <?php echo $statusClass; ?>"><?php echo $sol['estado']; ?></span></div>
                        </div>
                        <?php
    endforeach;
endif; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Modal CREATE Solicitud -->
    <div id="modalCreateSolicitud" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.85); z-index:6000; justify-content:center; align-items:center;">
        <div style="background:var(--card-bg); padding:30px; border-radius:24px; width:90%; max-width:500px; box-shadow:var(--shadow-xl);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="margin:0; font-size:20px; font-weight:800; color:var(--text-main);">Nueva Solicitud</h2>
                <button onclick="document.getElementById('modalCreateSolicitud').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-secondary);"><i class="fas fa-times"></i></button>
            </div>
            
            <form action="index.php?action=create_solicitud" method="POST">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-size:13px; font-weight:700; color:var(--text-main);">Departamento / Área</label>
                    <select name="categoria" required style="width:100%; border:1px solid var(--border-subtle); background:var(--input-bg); padding:12px 15px; border-radius:12px; font-size:14px; color:var(--text-main);">
                        <option value="Sistemas (IT)">Sistemas (IT) Accessos/Hardware</option>
                        <option value="Recursos Humanos">Recursos Humanos / Beneficios</option>
                        <option value="Vacaciones">Solicitud de Vacaciones</option>
                        <option value="Finanzas">Finanzas / Reembolsos</option>
                        <option value="Mantenimiento Oficina">Mantenimiento de Oficina</option>
                    </select>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-size:13px; font-weight:700; color:var(--text-main);">Motivo de la solicitud</label>
                    <input type="text" name="titulo" required placeholder="Ej: Problemas con VPN" style="width:100%; border:1px solid var(--border-subtle); background:var(--input-bg); padding:12px 15px; border-radius:12px; font-size:14px; color:var(--text-main);">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:8px; font-size:13px; font-weight:700; color:var(--text-main);">Descripción detallada</label>
                    <textarea name="descripcion" rows="4" required placeholder="Explica tu solicitud en detalle..." style="width:100%; border:1px solid var(--border-subtle); background:var(--input-bg); padding:12px 15px; border-radius:12px; font-size:14px; color:var(--text-main); font-family:inherit; resize:vertical;"></textarea>
                </div>
                
                <button type="submit" style="width:100%; background:#10B981; color:white; border:none; padding:15px; border-radius:12px; font-weight:800; font-size:15px; cursor:pointer; transition:0.2s;">
                    Enviar Solicitud
                </button>
            </form>
        </div>
    </div>

    <!-- Modal VIEW / UPDATE Solicitud -->
    <div id="modalReadSolicitud" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.85); z-index:6000; justify-content:center; align-items:center;">
        <div style="background:var(--card-bg); padding:30px; border-radius:24px; width:90%; max-width:600px; box-shadow:var(--shadow-xl);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 style="margin:0; font-size:20px; font-weight:800; color:var(--text-main);">Detalle de Solicitud</h2>
                <button onclick="document.getElementById('modalReadSolicitud').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-secondary);"><i class="fas fa-times"></i></button>
            </div>
            
            <div style="background:var(--input-bg); padding:20px; border-radius:12px; margin-bottom:20px;">
                <div style="font-size:12px; color:var(--text-secondary); font-weight:800; text-transform:uppercase; margin-bottom:5px;" id="readCat">Sistemas (IT) • REQ-#0012</div>
                <div style="font-size:18px; font-weight:800; color:var(--text-main); margin-bottom:15px;" id="readTitle">Titulo</div>
                <div style="font-size:14px; color:var(--text-main); line-height:1.6;" id="readDesc">Descripcion...</div>
                
                <div style="margin-top:15px; padding-top:15px; border-top:1px dashed var(--border-subtle);">
                    <div style="font-size:12px; font-weight:800; color:var(--text-secondary); margin-bottom:5px;">Respuesta del Administrador:</div>
                    <div style="font-size:14px; color:var(--text-main);" id="readAdminResponse"><i>El administrador aún no ha respondido.</i></div>
                </div>
            </div>

            <?php if ($isAdmin): ?>
            <!-- ADMIN CONTROLS -->
            <form action="index.php?action=update_solicitud_status" method="POST" style="border-top:1px solid var(--border-subtle); padding-top:20px;">
                <input type="hidden" name="id" id="updateId">
                <input type="hidden" name="estado" id="updateStatusValue">
                
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-size:13px; font-weight:700; color:var(--text-main);">Respuesta (Opcional)</label>
                    <textarea name="respuesta_admin" id="updateRespuesta" rows="3" style="width:100%; border:1px solid var(--border-subtle); background:var(--input-bg); padding:10px; border-radius:10px; font-size:14px; color:var(--text-main); font-family:inherit; resize:vertical;"></textarea>
                </div>
                
                <div style="display:flex; gap:10px;">
                    <button type="submit" onclick="document.getElementById('updateStatusValue').value='En Progreso'" style="flex:1; background:rgba(59, 130, 246, 0.1); color:#3B82F6; border:1px solid rgba(59, 130, 246, 0.2); padding:12px; border-radius:10px; font-weight:800; font-size:14px; cursor:pointer;">En Progreso</button>
                    <button type="submit" onclick="document.getElementById('updateStatusValue').value='Resuelto'" style="flex:1; background:rgba(16, 185, 129, 0.1); color:#10B981; border:1px solid rgba(16, 185, 129, 0.2); padding:12px; border-radius:10px; font-weight:800; font-size:14px; cursor:pointer;">Marcar Resuelto</button>
                </div>
            </form>
            <?php
endif; ?>
        </div>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle'); 
        const body = document.body;
        if(localStorage.getItem('theme') === 'dark') body.classList.add('dark-mode');
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light'); });
        
        document.getElementById('openSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('active');
            let o = document.getElementById('sidebarOverlay');
            if(!o) { o = document.createElement('div'); o.className='sidebar-overlay active'; o.id='sidebarOverlay'; document.body.appendChild(o); }
            else { o.classList.add('active'); }
            o.onclick = () => { document.getElementById('sidebar').classList.remove('active'); o.classList.remove('active'); }
        });

        function filterTickets(status) {
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            if(status === 'all') document.getElementById('f-all').classList.add('active');
            if(status === 'Pendiente') document.getElementById('f-pendiente').classList.add('active');
            if(status === 'En Progreso') document.getElementById('f-progreso').classList.add('active');
            if(status === 'Resuelto') document.getElementById('f-resuelto').classList.add('active');

            const cards = document.querySelectorAll('.ticket-card');
            cards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                if (status === 'all') {
                    card.style.display = 'flex';
                } else if (status === 'Resuelto' && (cardStatus === 'Resuelto' || cardStatus === 'Cerrado')) {
                    card.style.display = 'flex';
                } else if (cardStatus === status) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openTicketModal(ticket) {
            document.getElementById('readCat').innerText = ticket.categoria + ' • REQ-#' + String(ticket.id).padStart(4, '0') + ' • ' + ticket.estado;
            document.getElementById('readTitle').innerText = ticket.titulo;
            document.getElementById('readDesc').innerText = ticket.descripcion;
            
            const respBox = document.getElementById('readAdminResponse');
            if (ticket.respuesta_admin) {
                respBox.innerHTML = '<strong>Anotación:</strong> <br>' + ticket.respuesta_admin.replace(/\n/g, '<br>');
            } else {
                respBox.innerHTML = '<i>El administrador aún no ha respondido.</i>';
            }

            // If Admin, populate the update form
            const updateId = document.getElementById('updateId');
            if (updateId) {
                updateId.value = ticket.id;
                document.getElementById('updateRespuesta').value = ticket.respuesta_admin || '';
            }

            document.getElementById('modalReadSolicitud').style.display = 'flex';
        }
    </script>
</body>
</html>
