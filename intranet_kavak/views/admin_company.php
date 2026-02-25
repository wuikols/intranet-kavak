<?php
global $globalQuickLinks;

if (empty($_SESSION['p_empresa'])) {
    die("Acceso denegado. Privilegios insuficientes.");
}
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Company.php';
require_once 'models/Location.php';
require_once 'models/Role.php';

$db = (new Database())->getConnection();

$companyModel = new Company($db);
$sucursales = $companyModel->getSucursales();
$cargos = $companyModel->getCargos();

$roles = (new Role($db))->getAll(); // Fetch roles

$locationModel = new Location($db);
$comunas = $locationModel->getComunas();


$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Estructura Empresarial - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/intranet_kavak/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
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

        /* GRID DE TRES COLUMNAS */
        .company-sections-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; align-items: stretch; }
        @media (max-width: 1400px) { .company-sections-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 900px) { .company-sections-grid { grid-template-columns: 1fr; } }

        .company-card { background: var(--card-bg); border-radius: 20px; border: 1px solid var(--border-subtle); box-shadow: 0 4px 15px rgba(0,0,0,0.03); display: flex; flex-direction: column; height: 100%; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden; max-height: 600px;}
        .company-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px -15px rgba(37,99,235,0.15); border-color: var(--accent-color); }
        
        .section-header { display: flex; justify-content: space-between; align-items: center; padding: 25px 25px 20px 25px; border-bottom: 1px solid var(--border-subtle); background: var(--input-bg); }
        .section-title h3 { color: var(--text-main); margin: 0; font-size: 18px; font-weight: 800; display: flex; align-items: center; gap: 10px;}
        .section-title p { color: var(--text-secondary); margin: 5px 0 0 0; font-size: 12px; }
        
        .data-grid-container { padding: 0; flex: 1; overflow-y: auto; overflow-x: auto; background: var(--card-bg);}
        /* Custom Scrollbar for Grid */
        .data-grid-container::-webkit-scrollbar { width: 6px; height: 6px; }
        .data-grid-container::-webkit-scrollbar-track { background: transparent; }
        .data-grid-container::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }
        
        .data-grid { width: 100%; border-collapse: collapse; text-align: left; }
        .data-grid th { background: var(--input-bg); padding: 12px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-secondary); border-bottom: 1px solid var(--border-subtle); letter-spacing: 0.5px; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}
        .data-grid td { padding: 16px 25px; border-bottom: 1px solid var(--border-subtle); vertical-align: middle; transition: background 0.2s; font-size: 13px; color: var(--text-main);}
        .data-grid tbody tr:hover td { background: rgba(37, 99, 235, 0.03); }
        .data-grid tbody tr:last-child td { border-bottom: none; }

        .cell-primary { font-weight: 700; font-size: 15px; }
        .cell-secondary { color: var(--text-secondary); font-size: 13px; display: flex; align-items: center; gap: 6px; }
        .badge-code { background: var(--input-bg); border: 1px solid var(--border-subtle); padding: 4px 8px; border-radius: 6px; font-family: monospace; font-weight: 700; font-size: 12px; color: var(--accent-color);}

        .action-buttons { display: flex; gap: 8px; align-items: center; justify-content: flex-end;}
        .btn-icon { width: 32px; height: 32px; display: flex; justify-content: center; align-items: center; background: var(--input-bg); color: var(--text-secondary); border: 1px solid transparent; border-radius: 8px; cursor: pointer; transition: all 0.2s ease; font-size: 13px; text-decoration: none;}
        .btn-icon:hover { background: var(--card-bg); border-color: var(--border-subtle); color: var(--accent-color); box-shadow: var(--shadow-sm); transform: translateY(-2px); }
        .btn-icon.btn-delete:hover { color: #EF4444; border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.05); }

        .crud-modal { display: none; position: fixed; z-index: 5000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(6px); justify-content: center; align-items: center; padding: 20px;}
        .crud-modal-content { background: var(--card-bg); width: 100%; max-width: 550px; border-radius: 20px; padding: 35px; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); animation: popModal 0.3s cubic-bezier(0.16, 1, 0.3, 1); max-height: 90vh; overflow-y: auto;}
        @keyframes popModal { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .modal-close { position: absolute; top: 20px; right: 20px; background: var(--input-bg); color: var(--text-secondary); border: 1px solid var(--border-subtle); width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; font-size: 14px;}
        .modal-close:hover { background: #EF4444; color: white; border-color: #EF4444; transform: rotate(90deg); }

        .modal-header { margin-bottom: 25px; }
        .modal-header h3 { font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0 0 5px 0; display: flex; align-items: center; gap: 10px;}
        .modal-header p { font-size: 13px; color: var(--text-secondary); margin: 0; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        @media(max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
        
        .form-group { width: 100%; margin-bottom: 15px;}
        .form-label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input { width: 100%; padding: 12px 16px; border: 2px solid var(--border-subtle); border-radius: 10px; font-size: 14px; color: var(--text-main); background: var(--input-bg); transition: 0.3s; outline: none; font-weight: 500;}
        .form-input:focus { border-color: var(--accent-color); background: var(--card-bg); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); }
        select.form-input { cursor: pointer; }

        .btn-submit { width: 100%; padding: 14px; background: var(--accent-color); color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: var(--shadow-sm); display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px;}
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
$topbarTitle = 'Estructura Corporativa';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-msg" style="background: #DCFCE7; color: #166534; padding: 15px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; border: 1px solid #4ADE80;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php
endif; ?>

                <div class="company-sections-grid">
                    <div class="company-card">
                        <div class="section-header">
                            <div class="section-title">
                                <h3><i class="fas fa-map-marker-alt" style="color: var(--accent-color);"></i> HUBs y Sucursales</h3>
                                <p>Centros de operación físicos.</p>
                            </div>
                            <button onclick="openModal('createSucursalModal')" class="btn-login" style="padding: 8px 15px; font-size: 12px; border-radius:8px;"><i class="fas fa-plus"></i> Añadir</button>
                        </div>
                        <div class="data-grid-container">
                            <table class="data-grid">
                                <thead>
                                    <tr><th>Locación</th><th>Ubicación</th><th style="text-align: right;">Acciones</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sucursales as $s): ?>
                                    <tr>
                                        <td>
                                            <div class="cell-primary"><?php echo htmlspecialchars($s['nombre']); ?></div>
                                            <span class="badge-code" style="margin-top:5px; display:inline-block;"><?php echo htmlspecialchars($s['nombre_corto']); ?></span>
                                        </td>
                                        <td><div class="cell-secondary" style="flex-wrap:wrap;"><i class="fas fa-map-pin text-secondary"></i> <?php echo htmlspecialchars($s['direccion']); ?>, <?php echo htmlspecialchars($s['comuna_nombre'] ?? 'Sin Comuna'); ?></div></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick='populateEditSucursalModal(<?php echo json_encode($s, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-icon" title="Editar HUB"><i class="fas fa-pen"></i></button>
                                                <a href="index.php?action=delete_sucursal&id=<?php echo $s['id']; ?>" class="btn-icon btn-delete" title="Eliminar HUB" onclick="return confirm('¿Eliminar este HUB?');"><i class="fas fa-trash-alt"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="company-card">
                        <div class="section-header">
                            <div class="section-title">
                                <h3><i class="fas fa-briefcase" style="color: #D97706;"></i> Cargos Profesionales</h3>
                                <p>Roles laborales disponibles.</p>
                            </div>
                            <button onclick="openModal('createCargoModal')" class="btn-login" style="padding: 8px 15px; font-size: 12px; background: #D97706; border-radius:8px;"><i class="fas fa-plus"></i> Añadir</button>
                        </div>
                        <div class="data-grid-container">
                            <table class="data-grid">
                                <thead><tr><th>Título Posición</th><th style="text-align: right;">Acciones</th></tr></thead>
                                <tbody>
                                    <?php foreach ($cargos as $c): ?>
                                    <tr>
                                        <td class="cell-primary" style="color: var(--text-main);"><i class="fas fa-id-badge" style="color: #D97706; margin-right: 12px; font-size: 16px; opacity:0.8;"></i> <?php echo htmlspecialchars($c['nombre']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick='populateEditCargoModal(<?php echo json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-icon" title="Editar Cargo"><i class="fas fa-pen"></i></button>
                                                <a href="index.php?action=delete_cargo&id=<?php echo $c['id']; ?>" class="btn-icon btn-delete" title="Eliminar Cargo" onclick="return confirm('¿Eliminar este cargo permanentemente?');"><i class="fas fa-trash-alt"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="company-card" style="padding: 30px; display: flex; flex-direction: column;">
                        <div class="section-header">
                            <div class="section-title">
                                <h3><i class="fas fa-user-shield" style="color: #10B981;"></i> Sistema y Permisos</h3>
                                <p>Control granular de acceso.</p>
                            </div>
                            <button onclick="openModal('createRoleModal')" class="btn-login" style="padding: 8px 15px; font-size: 12px; background: #10B981; border-radius:8px;"><i class="fas fa-plus"></i> Añadir</button>
                        </div>
                        <div class="data-grid-container" style="flex: 1; overflow-y: auto;">
                            <table class="data-grid">
                                <thead><tr><th>Nombre de Rol</th><th style="text-align: right;">Acciones</th></tr></thead>
                                <tbody>
                                    <?php foreach ($roles as $r): ?>
                                    <tr>
                                        <td>
                                            <div class="cell-primary" style="color: var(--text-main);"><i class="fas fa-key" style="color: #10B981; opacity:0.8; margin-right: 12px; font-size: 16px;"></i> <?php echo htmlspecialchars($r['nombre']); ?></div>
                                            <div class="cell-secondary" style="font-size: 10px; margin-top:8px; flex-wrap:wrap;">
                                                <?php if ($r['p_noticias'])
        echo "<span class='badge-code' style='background:rgba(37,99,235,0.1); color:#3B82F6; border:none; padding:3px 6px;'>Noticias</span>"; ?>
                                                <?php if ($r['p_usuarios'])
        echo "<span class='badge-code' style='background:rgba(245,158,11,0.1); color:#F59E0B; border:none; padding:3px 6px;'>Usuarios</span>"; ?>
                                                <?php if ($r['p_empresa'])
        echo "<span class='badge-code' style='background:rgba(16,185,129,0.1); color:#10B981; border:none; padding:3px 6px;'>Empresa</span>"; ?>
                                                <?php if ($r['p_dashboard_admin'])
        echo "<span class='badge-code' style='background:rgba(139,92,246,0.1); color:#8B5CF6; border:none; padding:3px 6px;'>Analytics</span>"; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($r['id'] != 1): // Evitar editar Hardcoded Super Admin ?>
                                            <div class="action-buttons">
                                                <button onclick='populateEditRoleModal(<?php echo json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-icon" title="Editar Rol"><i class="fas fa-pen"></i></button>
                                                <a href="index.php?action=delete_rol&id=<?php echo $r['id']; ?>" class="btn-icon btn-delete" title="Eliminar Rol" onclick="return confirm('¿Eliminar este rol? Los usuarios asociados podrían perder acceso.');"><i class="fas fa-trash-alt"></i></a>
                                            </div>
                                            <?php
    else: ?>
                                                <span style="font-size:9px; color:#EF4444; font-weight:800; background:rgba(239,68,68,0.1); padding:4px 8px; border-radius:10px; text-transform:uppercase;">Intocable</span>
                                            <?php
    endif; ?>
                                        </td>
                                    </tr>
                                    <?php
endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>

    <div id="createSucursalModal" class="crud-modal">
        <div class="crud-modal-content">
            <button class="modal-close" onclick="closeModal('createSucursalModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-building" style="color: var(--accent-color);"></i> Registrar Nuevo HUB</h3>
                <p>Añade una nueva locación física o centro de operaciones.</p>
            </div>
            <form action="index.php?action=create_sucursal" method="POST">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">Nombre del HUB *</label><input type="text" name="nombre" class="form-input" required placeholder="Ej: Kavak Mall Plaza"></div>
                    <div class="form-group"><label class="form-label">Código Corto (3-5 Letras) *</label><input type="text" name="nombre_corto" class="form-input" required placeholder="Ej: KMP" maxlength="5" style="text-transform: uppercase;"></div>
                </div>
                <div class="form-group"><label class="form-label">Dirección Física *</label><input type="text" name="direccion" class="form-input" required placeholder="Av. Principal 123, Oficina 401"></div>
                <div class="form-group">
                    <label class="form-label">Comuna / Sector *</label>
                    <select name="comuna_id" class="form-input" required>
                        <option value="">Seleccione una comuna...</option>
                        <?php foreach ($comunas as $com): ?><option value="<?php echo $com['id']; ?>"><?php echo htmlspecialchars($com['nombre']); ?></option><?php
endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar HUB</button>
            </form>
        </div>
    </div>

    <div id="editSucursalModal" class="crud-modal">
        <div class="crud-modal-content">
            <button class="modal-close" onclick="closeModal('editSucursalModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color: #059669;"></i> Modificar HUB</h3>
                <p>Actualiza la información de la locación seleccionada.</p>
            </div>
            <form action="index.php?action=update_sucursal" method="POST">
                <input type="hidden" name="id" id="edit_sucursal_id">
                <div class="form-grid">
                    <div class="form-group"><label class="form-label">Nombre del HUB</label><input type="text" name="nombre" id="edit_sucursal_nombre" class="form-input" required></div>
                    <div class="form-group"><label class="form-label">Código Corto</label><input type="text" name="nombre_corto" id="edit_sucursal_corto" class="form-input" required maxlength="5" style="text-transform: uppercase;"></div>
                </div>
                <div class="form-group"><label class="form-label">Dirección Física</label><input type="text" name="direccion" id="edit_sucursal_direccion" class="form-input" required></div>
                <div class="form-group">
                    <label class="form-label">Comuna / Sector</label>
                    <select name="comuna_id" id="edit_sucursal_comuna" class="form-input" required>
                        <?php foreach ($comunas as $com): ?><option value="<?php echo $com['id']; ?>"><?php echo htmlspecialchars($com['nombre']); ?></option><?php
endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit btn-update"><i class="fas fa-sync-alt"></i> Actualizar Datos</button>
            </form>
        </div>
    </div>

    <div id="createCargoModal" class="crud-modal">
        <div class="crud-modal-content" style="max-width: 450px;">
            <button class="modal-close" onclick="closeModal('createCargoModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-briefcase" style="color: #D97706;"></i> Nuevo Cargo Profesional</h3>
                <p>Define un nuevo título de posición en la empresa.</p>
            </div>
            <form action="index.php?action=create_cargo" method="POST">
                <div class="form-group"><label class="form-label">Título del Cargo *</label><input type="text" name="nombre" class="form-input" required placeholder="Ej: Analista de Operaciones Senior"></div>
                <button type="submit" class="btn-submit" style="background: #D97706;"><i class="fas fa-save"></i> Crear Cargo</button>
            </form>
        </div>
    </div>

    <div id="editCargoModal" class="crud-modal">
        <div class="crud-modal-content" style="max-width: 450px;">
            <button class="modal-close" onclick="closeModal('editCargoModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color: #059669;"></i> Renombrar Cargo</h3>
                <p>Modifica el título de la posición seleccionada.</p>
            </div>
            <form action="index.php?action=update_cargo" method="POST">
                <input type="hidden" name="id" id="edit_cargo_id">
                <div class="form-group"><label class="form-label">Título del Cargo</label><input type="text" name="nombre" id="edit_cargo_nombre" class="form-input" required></div>
                <button type="submit" class="btn-submit btn-update"><i class="fas fa-sync-alt"></i> Actualizar Título</button>
            </form>
        </div>
    </div>

    <!-- MODAL CREAR ROL -->
    <div id="createRoleModal" class="crud-modal">
        <div class="crud-modal-content" style="max-width: 500px;">
            <button class="modal-close" onclick="closeModal('createRoleModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-user-shield" style="color: #10B981;"></i> Nuevo Rol de Usuario</h3>
                <p>Define un rol y habilita sus accesos modulares.</p>
            </div>
            <form action="index.php?action=create_rol" method="POST">
                <div class="form-group"><label class="form-label">Nombre del Grupo/Rol *</label><input type="text" name="nombre" class="form-input" required placeholder="Ej: Especialista de Marketing"></div>
                
                <label class="form-label" style="margin-top:20px; border-bottom:1px solid var(--border-subtle); padding-bottom:5px;">Permisos Globales</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px; font-size:13px; color:var(--text-main);">
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_noticias" value="1"> <i class="fas fa-bullhorn" style="color:#3B82F6;"></i> Gestionar Comunicaciones (Noticias)</label>
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_usuarios" value="1"> <i class="fas fa-users" style="color:#F59E0B;"></i> Gestionar Usuarios</label>
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_empresa" value="1"> <i class="fas fa-building" style="color:#10B981;"></i> Estructura (Hubs/Roles)</label>
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_dashboard_admin" value="1"> <i class="fas fa-chart-line" style="color:#8B5CF6;"></i> Dashboard Analytics</label>
                </div>

                <button type="submit" class="btn-submit" style="background: #10B981; margin-top:25px;"><i class="fas fa-save"></i> Crear Rol</button>
            </form>
        </div>
    </div>

    <!-- MODAL EDITAR ROL -->
    <div id="editRoleModal" class="crud-modal">
        <div class="crud-modal-content" style="max-width: 500px;">
            <button class="modal-close" onclick="closeModal('editRoleModal')"><i class="fas fa-times"></i></button>
            <div class="modal-header">
                <h3><i class="fas fa-edit" style="color: #10B981;"></i> Editar Permisos de Rol</h3>
                <p>Actualiza a qué partes del sistema tiene acceso este grupo.</p>
            </div>
            <form action="index.php?action=update_rol" method="POST">
                <input type="hidden" name="id" id="edit_rol_id_val">
                <div class="form-group"><label class="form-label">Nombre del Grupo/Rol *</label><input type="text" name="nombre" id="edit_rol_nombre" class="form-input" required></div>
                
                <label class="form-label" style="margin-top:20px; border-bottom:1px solid var(--border-subtle); padding-bottom:5px;">Permisos Globales</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:10px; font-size:13px; color:var(--text-main);">
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_noticias" id="edit_p_noticias" value="1"> <i class="fas fa-bullhorn" style="color:#3B82F6;"></i> Comunicaciones</label>
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_usuarios" id="edit_p_usuarios" value="1"> <i class="fas fa-users" style="color:#F59E0B;"></i> Usuarios</label>
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_empresa" id="edit_p_empresa" value="1"> <i class="fas fa-building" style="color:#10B981;"></i> Estructura</label>
                    <label style="display:flex; align-items:center; gap:8px;"><input type="checkbox" name="p_dashboard_admin" id="edit_p_dashboard" value="1"> <i class="fas fa-chart-line" style="color:#8B5CF6;"></i> Analytics</label>
                </div>

                <button type="submit" class="btn-submit btn-update" style="margin-top:25px;"><i class="fas fa-sync-alt"></i> Actualizar Permisos</button>
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
        const themeToggle = document.getElementById('themeToggle'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') body.classList.add('dark-mode');
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light'); });
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }
        window.onclick = function(event) { if (event.target.classList.contains('crud-modal')) { event.target.style.display = 'none'; }}

        function populateEditSucursalModal(data) {
            document.getElementById('edit_sucursal_id').value = data.id;
            document.getElementById('edit_sucursal_nombre').value = data.nombre;
            document.getElementById('edit_sucursal_corto').value = data.nombre_corto;
            document.getElementById('edit_sucursal_direccion').value = data.direccion;
            document.getElementById('edit_sucursal_comuna').value = data.comuna_id;
            openModal('editSucursalModal');
        }

        function populateEditCargoModal(data) {
            document.getElementById('edit_cargo_id').value = data.id;
            document.getElementById('edit_cargo_nombre').value = data.nombre;
            openModal('editCargoModal');
        }

        function populateEditRoleModal(data) {
            document.getElementById('edit_rol_id_val').value = data.id;
            document.getElementById('edit_rol_nombre').value = data.nombre;
            document.getElementById('edit_p_noticias').checked = data.p_noticias == 1;
            document.getElementById('edit_p_usuarios').checked = data.p_usuarios == 1;
            document.getElementById('edit_p_empresa').checked = data.p_empresa == 1;
            document.getElementById('edit_p_dashboard').checked = data.p_dashboard_admin == 1;
            openModal('editRoleModal');
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