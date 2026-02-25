<?php
global $globalQuickLinks;
if (empty($_SESSION['p_usuarios'])) {
    die("Acceso denegado. Privilegios insuficientes.");
}
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Company.php';
require_once 'models/Role.php';

$db = (new Database())->getConnection();

$users = (new User($db))->getAllUsers();
$company = new Company($db);

$sucursales = $company->getSucursales();

$cargos = $company->getCargos();

$roles = (new Role($db))->getAll();

$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Gestión de Empleados - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }

        /* Topbar */
        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
        .ql-admin-add { background: rgba(37,99,235,0.1); color: var(--accent-color); border: 1px dashed var(--accent-color); }
        .btn-delete-ql { position: absolute; top: -5px; right: -5px; background: #EF4444; color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 9px; display: none; align-items: center; justify-content: center; border: none; cursor: pointer; }
        .ql-btn:hover .btn-delete-ql { display: flex; }

        .module-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .module-title h2 { color: var(--text-main); margin: 0; font-size: 26px; font-weight: 800; display: flex; align-items: center; gap: 12px;}
        .module-title p { color: var(--text-secondary); margin: 5px 0 0 0; font-size: 14px; }
        
        .data-grid-container { background: var(--card-bg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); overflow-x: auto; }
        .data-grid { width: 100%; border-collapse: collapse; text-align: left; white-space: nowrap; }
        .data-grid th { background: var(--input-bg); padding: 16px 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); border-bottom: 2px solid var(--border-subtle); letter-spacing: 0.5px; }
        .data-grid td { padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); vertical-align: middle; transition: background 0.2s; color: var(--text-main); }
        .data-grid tbody tr:hover td { background: rgba(37, 99, 235, 0.02); }
        .data-grid tbody tr:last-child td { border-bottom: none; }

        .cell-user-info { display: flex; flex-direction: column; gap: 4px; }
        .cell-user-name { font-weight: 700; color: var(--text-main); font-size: 15px; }
        .cell-user-email { font-size: 12px; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; }
        .cell-job-info { display: flex; flex-direction: column; gap: 4px; }
        .cell-job-title { color: var(--text-main); font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 6px; }
        .cell-job-hub { color: var(--accent-color); font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 6px; }

        .role-badge { display: inline-flex; align-items: center; justify-content: center; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .role-super { background: rgba(245, 158, 11, 0.15); color: #D97706; border: 1px solid rgba(245, 158, 11, 0.3); }
        .role-admin { background: rgba(124, 58, 237, 0.1); color: #7C3AED; border: 1px solid rgba(124, 58, 237, 0.3); }
        .role-user { background: var(--input-bg); color: var(--text-secondary); border: 1px solid var(--border-subtle); }

        .action-buttons { display: flex; gap: 8px; align-items: center; justify-content: flex-end;}
        .btn-icon { width: 34px; height: 34px; display: flex; justify-content: center; align-items: center; background: var(--input-bg); color: var(--text-secondary); border: 1px solid transparent; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; font-size: 14px; text-decoration: none;}
        .btn-icon:hover { background: var(--card-bg); border-color: var(--border-subtle); color: var(--accent-color); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
        .btn-icon.btn-delete:hover { color: #EF4444; border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.05); }

        .crud-modal { display: none; position: fixed; z-index: 5000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); justify-content: center; align-items: center; padding: 20px;}
        .crud-modal-content { background: var(--card-bg); width: 100%; max-width: 650px; border-radius: 20px; padding: 40px; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: popModal 0.3s cubic-bezier(0.16, 1, 0.3, 1); max-height: 90vh; overflow-y: auto;}
        @keyframes popModal { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .modal-close { position: absolute; top: 20px; right: 20px; background: var(--input-bg); color: var(--text-secondary); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; font-size: 16px;}
        .modal-close:hover { background: #EF4444; color: white; border-color: #EF4444; transform: rotate(90deg); }

        .modal-header { margin-bottom: 30px; }
        .modal-header h3 { font-size: 24px; font-weight: 800; color: var(--text-main); margin: 0 0 5px 0; display: flex; align-items: center; gap: 10px;}
        .modal-header p { font-size: 14px; color: var(--text-secondary); margin: 0; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        @media(max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
        
        .form-group { width: 100%; margin-bottom: 15px;}
        .form-label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { width: 100%; padding: 12px 16px; border: 2px solid var(--border-subtle); border-radius: 10px; font-size: 14px; color: var(--text-main); background: var(--input-bg); transition: 0.3s; outline: none; font-weight: 500;}
        .form-input:focus { border-color: var(--accent-color); background: var(--card-bg); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        
        .btn-submit { width: 100%; padding: 16px; background: var(--accent-color); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: var(--shadow-sm); display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px;}
        .btn-submit:hover { background: var(--corporate-blue); transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .btn-submit.btn-update { background: #059669; }
        .btn-submit.btn-update:hover { background: #047857; }
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Panel de Control - Empleados';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div class="module-header">
                    <div class="module-title">
                        <h2><i class="fas fa-users-cog" style="color: var(--accent-color);"></i> Master Data de Empleados</h2>
                        <p>Directorio centralizado de accesos, roles y asignación corporativa.</p>
                    </div>
                    <div style="display:flex; gap: 15px; align-items:center;">
                        <input type="text" id="userSearch" placeholder="Buscar por nombre, RUT o correo..." autocomplete="off" style="padding:10px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-main); font-size:14px; width:260px;">
                        <button onclick="openModal('createModal')" class="btn-login" style="padding: 12px 24px;"><i class="fas fa-user-plus"></i> Dar de Alta Empleado</button>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?>
                    <div class="success-msg" style="background: #DCFCE7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; border: 1px solid #4ADE80;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php
endif; ?>

                <div class="data-grid-container">
                    <table class="data-grid" id="usersTable">
                        <thead>
                            <tr>
                                <th>RUT Oficial</th>
                                <th>Colaborador</th>
                                <th>Cargo y Ubicación</th>
                                <th>Nivel de Acceso</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u):
    $rol = strtoupper($u['rol_nombre'] ?? 'SIN ROL');
    $badgeClass = 'role-user';
    if (strpos($rol, 'SUPER') !== false) {
        $badgeClass = 'role-super';
    }
    elseif (strpos($rol, 'ADMIN') !== false) {
        $badgeClass = 'role-admin';
    }
    $searchStr = strtolower(($u['rut'] ?? '') . ' ' . ($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '') . ' ' . ($u['email'] ?? ''));
?>
                            <tr class="user-row" data-search="<?php echo htmlspecialchars($searchStr); ?>">
                                <td class="cell-rut rut-display"><span><?php echo htmlspecialchars($u['rut'] ?? '-'); ?></span></td>
                                <td>
                                    <div class="cell-user-info">
                                        <span class="cell-user-name"><?php echo htmlspecialchars(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')); ?></span>
                                        <span class="cell-user-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($u['email'] ?? 'Sin correo'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-job-info">
                                        <span class="cell-job-title"><i class="fas fa-briefcase" style="color: var(--text-secondary);"></i> <?php echo htmlspecialchars($u['cargo_nombre'] ?? 'No Asignado'); ?></span>
                                        <span class="cell-job-hub"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($u['sucursal_nombre'] ?? 'Sin Base'); ?></span>
                                    </div>
                                </td>
                                <td><span class="role-badge <?php echo $badgeClass; ?>"><?php echo $rol; ?></span></td>
                                <td style="text-align: right;">
                                    <div class="action-buttons">
                                        <button onclick='populateEditModal(<?php echo json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-icon" title="Editar Expediente"><i class="fas fa-pen"></i></button>
                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                        <a href="index.php?action=delete_user&id=<?php echo $u['id']; ?>" class="btn-icon btn-delete" title="Dar de Baja" onclick="return confirm('¿Estás seguro de eliminar a este usuario?');"><i class="fas fa-trash-alt"></i></a>
                                        <?php
    endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php
endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <div id="createModal" class="crud-modal">
        <div class="crud-modal-content">
            <button class="modal-close" onclick="closeModal('createModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-user-plus" style="color: var(--accent-color);"></i> Alta de Nuevo Empleado</h3>
                <p>Ingresa la información institucional para generar las credenciales de acceso.</p>
            </div>
            <form action="index.php?action=create_user_admin" method="POST">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">RUT Oficial *</label><input type="text" name="rut" class="form-input rut-input" required placeholder="Ej: 12345678-9"></div>
                    <div class="form-group"><label class="form-label">Correo Corporativo *</label><input type="email" name="email" class="form-input" required placeholder="usuario@kavak.com"></div>
                    <div class="form-group"><label class="form-label">Nombres *</label><input type="text" name="nombre" class="form-input" required placeholder="Nombres del empleado"></div>
                    <div class="form-group"><label class="form-label">Apellidos *</label><input type="text" name="apellido" class="form-input" required placeholder="Apellidos completos"></div>
                    
                    <div class="form-group"><label class="form-label">Fecha de Nacimiento</label><input type="date" name="fecha_nacimiento" class="form-input"></div>
                    
                    <div class="form-group"><label class="form-label">Fecha de Ingreso</label><input type="date" name="fecha_ingreso" class="form-input" required></div>

                    <div class="form-group">
                        <label class="form-label">Cargo Asignado</label>
                        <select name="cargo_id" class="form-input"><?php foreach ($cargos as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option><?php
endforeach; ?></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">HUB / Locación Base</label>
                        <select name="sucursal_id" class="form-input"><?php foreach ($sucursales as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nombre']); ?></option><?php
endforeach; ?></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nivel de Privilegios</label>
                        <select name="rol_id" class="form-input"><?php foreach ($roles as $r): ?><option value="<?php echo $r['id']; ?>" <?php if ($r['id'] == 3)
        echo 'selected'; ?>><?php echo htmlspecialchars($r['nombre']); ?></option><?php
endforeach; ?></select>
                    </div>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-check-circle"></i> Registrar en Plataforma</button>
            </form>
        </div>
    </div>
    
    <div id="editModal" class="crud-modal">
        <div class="crud-modal-content">
            <button class="modal-close" onclick="closeModal('editModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-user-edit" style="color: #059669;"></i> Actualizar Expediente</h3>
                <p>Modifica los datos administrativos, el cargo o los permisos del empleado.</p>
            </div>
            <form action="index.php?action=update_user_admin" method="POST">
                <input type="hidden" name="id" id="edit_id">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">RUT Oficial</label><input type="text" name="rut" id="edit_rut" class="form-input rut-input" required></div>
                    <div class="form-group"><label class="form-label">Correo Corporativo</label><input type="email" name="email" id="edit_email" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Nombres</label><input type="text" name="nombre" id="edit_nombre" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Apellidos</label><input type="text" name="apellido" id="edit_apellido" class="form-input" required></div>
                    
                    <div class="form-group"><label class="form-label">Fecha de Nacimiento</label><input type="date" name="fecha_nacimiento" id="edit_nacimiento" class="form-input"></div>
                    
                    <div class="form-group"><label class="form-label">Fecha de Ingreso</label><input type="date" name="fecha_ingreso" id="edit_ingreso" class="form-input"></div>

                    <div class="form-group">
                        <label class="form-label">Cargo Asignado</label>
                        <select name="cargo_id" id="edit_cargo_id" class="form-input"><?php foreach ($cargos as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option><?php
endforeach; ?></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">HUB / Locación Base</label>
                        <select name="sucursal_id" id="edit_sucursal_id" class="form-input"><?php foreach ($sucursales as $s): ?><option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nombre']); ?></option><?php
endforeach; ?></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nivel de Privilegios</label>
                        <select name="rol_id" id="edit_rol_id" class="form-input"><?php foreach ($roles as $r): ?><option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option><?php
endforeach; ?></select>
                    </div>
                </div>
                <button type="submit" class="btn-submit btn-update"><i class="fas fa-sync-alt"></i> Guardar Cambios</button>
            </form>
        </div>
    </div>

    <?php if ($isSuperAdmin): ?>
    <div id="modalAddQuickLink" class="crud-modal" style="z-index: 6000;">
        <div class="crud-modal-content" style="max-width: 400px; height: auto; padding: 30px;">
            <button class="modal-close" onclick="document.getElementById('modalAddQuickLink').style.display='none'" style="background: var(--input-bg); color: var(--text-main);"><i class="fas fa-times"></i></button>
            <h3 style="margin-top:0; font-weight:800; color:var(--text-main); font-size: 18px; margin-bottom: 20px;"><i class="fas fa-external-link-alt" style="color:var(--accent-color);"></i> Nuevo Acceso Directo</h3>
            <form action="index.php?action=create_quicklink" method="POST">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">Nombre de la Plataforma</label>
                    <input type="text" name="nombre" style="width: 100%; padding: 12px 16px; border: 2px solid var(--border-subtle); border-radius: 10px; font-size: 14px; background: var(--input-bg); color: var(--text-main); outline: none;" required placeholder="Ej: Buk, SAP, Gmail">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">URL del Sistema</label>
                    <input type="url" name="url" style="width: 100%; padding: 12px 16px; border: 2px solid var(--border-subtle); border-radius: 10px; font-size: 14px; background: var(--input-bg); color: var(--text-main); outline: none;" required placeholder="https://...">
                </div>
                <div class="form-group" style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase;">Clase de Icono</label>
                    <input type="text" name="icono" style="width: 100%; padding: 12px 16px; border: 2px solid var(--border-subtle); border-radius: 10px; font-size: 14px; background: var(--input-bg); color: var(--text-main); outline: none;" required placeholder="Ej: fas fa-car">
                </div>
                <button type="submit" style="width: 100%; background: var(--accent-color); color: white; border: none; padding: 14px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s;"><i class="fas fa-save"></i> Guardar Enlace</button>
            </form>
        </div>
    </div>
    <?php
endif; ?>

    <script>
        const themeToggle = document.getElementById('themeToggle'); const themeIcon = document.getElementById('themeIcon'); const themeText = document.getElementById('themeText'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') { body.classList.add('dark-mode'); if(themeIcon) themeIcon.className = 'fas fa-sun'; if(themeText) themeText.textContent = 'Modo Claro'; }
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); const isDark = body.classList.contains('dark-mode'); localStorage.setItem('theme', isDark ? 'dark' : 'light'); if(themeIcon) themeIcon.className = isDark ? 'fas fa-sun' : 'fas fa-moon'; if(themeText) themeText.textContent = isDark ? 'Modo Oscuro' : 'Modo Claro'; });
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        const searchInput = document.getElementById('userSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.user-row').forEach(row => { row.style.display = row.getAttribute('data-search').includes(term) ? '' : 'none'; });
            });
        }

        function formatRut(input) { let r = input.value.replace(/[^0-9kK]/g, '').toUpperCase(); if (r.length > 1) { input.value = r.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '-' + r.slice(-1); } else { input.value = r; } }
        document.querySelectorAll('.rut-input').forEach(i => i.addEventListener('input', function() { formatRut(this); }));
        document.querySelectorAll('.rut-display span').forEach(el => { let t = { value: el.innerText }; formatRut(t); el.innerText = t.value; });

        function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
        window.onclick = function(event) { if (event.target.classList.contains('crud-modal')) { event.target.style.display = 'none'; } }

        function populateEditModal(userData) {
            document.getElementById('edit_id').value = userData.id;
            document.getElementById('edit_rut').value = userData.rut || '';
            document.getElementById('edit_email').value = userData.email || '';
            document.getElementById('edit_nombre').value = userData.nombre || '';
            document.getElementById('edit_apellido').value = userData.apellido || '';
            document.getElementById('edit_cargo_id').value = userData.cargo_id || 1;
            document.getElementById('edit_sucursal_id').value = userData.sucursal_id || 1;
            document.getElementById('edit_rol_id').value = userData.rol_id || 3;
            document.getElementById('edit_ingreso').value = userData.fecha_ingreso || '';
            document.getElementById('edit_nacimiento').value = userData.fecha_nacimiento || ''; // CARGAR FECHA DE NACIMIENTO
            formatRut(document.getElementById('edit_rut'));
            openModal('editModal');
        }
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

        // Auto-fetch unread counts every minute
        setInterval(fetchNotifications, 60000);
        document.addEventListener("DOMContentLoaded", fetchNotifications);
    </script>
</body>
</html>