<?php
if (!isset($_SESSION['user_id']) || !isAdmin()) {
    header("Location: index.php?action=login");
    exit;
}
require_once 'config/database.php';
require_once 'models/User.php';

$db = (new Database())->getConnection();
$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Administrar Tips - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/intranet_kavak/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .admin-header { background: linear-gradient(135deg, var(--corporate-blue) 0%, #0F172A 100%); color: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(30, 58, 138, 0.5); margin-bottom: 30px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;}
        .admin-header h1 { margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -1px; }
        .admin-header p { margin: 10px 0 0 0; font-size: 15px; opacity: 0.9; }
        .ah-icon { font-size: 100px; position: absolute; right: 2%; top: -10px; opacity: 0.1; transform: rotate(15deg); }
        .btn-new { background: white; color: var(--corporate-blue); padding: 12px 24px; border-radius: 12px; font-weight: 800; font-size: 14px; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2); transition: 0.2s; position: relative; z-index: 10; border: none; cursor:pointer;}
        .btn-new:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.3); }

        .tips-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;}
        .tip-card { background: var(--card-bg); border-radius: 16px; padding: 25px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; transition: 0.3s; box-shadow: var(--shadow-sm); }
        .tip-card:hover { border-color: var(--corporate-blue); transform: translateY(-3px); box-shadow: var(--shadow-md); }
        .tip-inactive { opacity: 0.6; }
        .tip-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 10px; }
        .tip-content { font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px; flex: 1; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;}
        .tip-actions { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 15px;}
        .btn-toggle { background: none; border: none; cursor: pointer; font-size: 20px; color: var(--text-secondary); transition: 0.2s;}
        .btn-toggle.active { color: #10B981; }
        .btn-toggle:hover { transform: scale(1.1); }
        .action-btns button { background: var(--input-bg); border: none; width: 32px; height: 32px; border-radius: 8px; color: var(--text-secondary); cursor: pointer; transition: 0.2s; margin-left: 5px;}
        .action-btns button:hover { background: var(--corporate-blue); color: white; }
        .action-btns .btn-del:hover { background: #EF4444; color: white;}
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Panel de Control - Tips';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div class="admin-header">
                    <i class="fas fa-lightbulb ah-icon"></i>
                    <div>
                        <h1>Administrar Tips de Kavak OS</h1>
                        <p>Añade y configura los consejos que rotarán en el dashboard de todos los usuarios.</p>
                    </div>
                    <button class="btn-new" onclick="document.getElementById('modalTip').style.display='flex'"><i class="fas fa-plus"></i> Nuevo Tip</button>
                </div>

                <div class="tips-grid">
                    <?php if (empty($tips)): ?>
                        <div style="grid-column: 1/-1; text-align:center; padding:50px; background:var(--card-bg); border-radius:20px; border:1px solid var(--border-subtle);">
                            <i class="fas fa-ghost" style="font-size:40px; color:var(--text-secondary); opacity:0.5; margin-bottom:15px;"></i>
                            <h3 style="color:var(--text-main); font-weight:800; font-size:18px;">No hay tips creados</h3>
                        </div>
                    <?php
else:
    foreach ($tips as $tip): ?>
                        <div class="tip-card <?php echo $tip['activo'] ? '' : 'tip-inactive'; ?>">
                            <div class="tip-title"><?php echo htmlspecialchars($tip['titulo']); ?></div>
                            <div class="tip-content"><?php echo nl2br(htmlspecialchars($tip['contenido'])); ?></div>
                            <div class="tip-actions">
                                <a href="index.php?action=toggle_tip&id=<?php echo $tip['id']; ?>" class="btn-toggle <?php echo $tip['activo'] ? 'active' : ''; ?>" title="<?php echo $tip['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                    <i class="fas <?php echo $tip['activo'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                </a>
                                <div class="action-btns">
                                    <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($tip), ENT_QUOTES, 'UTF-8'); ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="btn-del" onclick="if(confirm('¿Eliminar tip?')) window.location='index.php?action=delete_tip&id=<?php echo $tip['id']; ?>'"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    <?php
    endforeach;
endif; ?>
                </div>

            </section>
        </main>
    </div>

    <!-- Modal Form -->
    <div id="modalTip" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.85); z-index:6000; justify-content:center; align-items:center;">
        <div style="background:var(--card-bg); padding:30px; border-radius:24px; width:90%; max-width:500px; box-shadow:var(--shadow-xl);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 id="modalTitle" style="margin:0; font-size:20px; font-weight:800; color:var(--text-main);">Nuevo Tip</h2>
                <button onclick="document.getElementById('modalTip').style.display='none'" style="background:none; border:none; font-size:20px; cursor:pointer; color:var(--text-secondary);"><i class="fas fa-times"></i></button>
            </div>
            
            <form id="tipForm" action="index.php?action=create_tip" method="POST">
                <input type="hidden" name="id" id="tipId">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:8px; font-size:13px; font-weight:700; color:var(--text-main);">Título del Tip</label>
                    <input type="text" name="titulo" id="tipTitulo" required style="width:100%; border:1px solid var(--border-subtle); background:var(--input-bg); padding:12px 15px; border-radius:12px; font-size:14px; color:var(--text-main);">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:8px; font-size:13px; font-weight:700; color:var(--text-main);">Instrucciones o Contenido</label>
                    <textarea name="contenido" id="tipContenido" rows="4" required style="width:100%; border:1px solid var(--border-subtle); background:var(--input-bg); padding:12px 15px; border-radius:12px; font-size:14px; color:var(--text-main); font-family:inherit; resize:vertical;"></textarea>
                </div>
                
                <button type="submit" style="width:100%; background:var(--corporate-blue); color:white; border:none; padding:15px; border-radius:12px; font-weight:800; font-size:15px; cursor:pointer; transition:0.2s;">
                    Guardar Tip
                </button>
            </form>
        </div>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle'); 
        const body = document.body;
        if(localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            document.getElementById('themeIcon').classList.replace('fa-moon', 'fa-sun');
            document.getElementById('themeText').innerText = 'Modo Claro';
        }
        themeToggle.addEventListener('click', () => { 
            body.classList.toggle('dark-mode'); 
            let isDark = body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light'); 
            document.getElementById('themeIcon').classList.replace(isDark ? 'fa-moon' : 'fa-sun', isDark ? 'fa-sun' : 'fa-moon');
            document.getElementById('themeText').innerText = isDark ? 'Modo Claro' : 'Modo Oscuro';
        });

        document.getElementById('openSidebar').addEventListener('click', () => {
            document.getElementById('sidebar').classList.add('active');
            let o = document.getElementById('sidebarOverlay');
            if(!o) { o = document.createElement('div'); o.className='sidebar-overlay active'; o.id='sidebarOverlay'; document.body.appendChild(o); }
            else { o.classList.add('active'); }
            o.onclick = () => { document.getElementById('sidebar').classList.remove('active'); o.classList.remove('active'); }
        });

        function openEditModal(tip) {
            document.getElementById('modalTitle').innerText = 'Editar Tip';
            document.getElementById('tipForm').action = 'index.php?action=update_tip';
            document.getElementById('tipId').value = tip.id;
            document.getElementById('tipTitulo').value = tip.titulo;
            document.getElementById('tipContenido').value = tip.contenido;
            document.getElementById('modalTip').style.display = 'flex';
        }
    </script>
</body>
</html>
