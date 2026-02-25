<?php
// ... (Todo el inicio del archivo es igual, solo cambiaremos la parte visual de la tarjeta izquierda)
// ...
global $globalQuickLinks;

if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}
require_once 'config/database.php';

require_once 'models/User.php';

require_once 'models/Location.php';
require_once 'models/QuickLink.php';

$db = (new Database())->getConnection();

$userModel = new User($db);
$currentUser = $userModel->getUserById($_SESSION['user_id']);

require_once 'models/Kudo.php';
$kudosRecibidos = (new Kudo($db))->getByUser($_SESSION['user_id']);

// Lógica Geográfica
$locationModel = new Location($db);
$regiones = $locationModel->getRegiones();
$provincias = [];
$comunas = [];

if (!empty($currentUser['region_id'])) {
    $provincias = $locationModel->getProvinciasByRegion($currentUser['region_id']);
}
if (!empty($currentUser['provincia_id'])) {
    $comunas = $locationModel->getComunasByProvincia($currentUser['provincia_id']);
}
elseif (!empty($currentUser['region_id'])) {
    $comunas = $locationModel->getComunasByRegion($currentUser['region_id']);
}

$quickLinksList = (new QuickLink($db))->getAll();
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Mi Perfil - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* (Copia aquí los estilos CSS del archivo anterior para mantener el diseño igual) */
        /* ... Estilos Sidebar, Topbar, Expediente, Grids ... */
        /* Para ahorrar espacio en la respuesta, asumo que mantienes el bloque <style> intacto del paso anterior */
        
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }

        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
        .ql-admin-add { background: rgba(37,99,235,0.1); color: var(--accent-color); border: 1px dashed var(--accent-color); }
        .ql-admin-add:hover { background: var(--accent-color); color: white; }
        .btn-delete-ql { position: absolute; top: -5px; right: -5px; background: #EF4444; color: white; border-radius: 50%; width: 16px; height: 16px; font-size: 9px; display: none; align-items: center; justify-content: center; border: none; cursor: pointer; }
        .ql-btn:hover .btn-delete-ql { display: flex; }

        /* Estilos Expediente */
        .expediente-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; }
        .expediente-title h1 { margin: 0 0 5px 0; font-size: 24px; font-weight: 800; color: var(--text-main); }
        .expediente-title p { margin: 0; font-size: 14px; color: var(--text-secondary); }
        .btn-edit-info { background: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; box-shadow: var(--shadow-sm);}
        .btn-edit-info:hover { background: var(--input-bg); border-color: var(--accent-color); color: var(--accent-color); transform: translateY(-2px); }

        .expediente-grid { display: grid; grid-template-columns: 320px 1fr; gap: 30px; align-items: start;}
        @media (max-width: 1024px) { .expediente-grid { grid-template-columns: 1fr; } }

        /* KAVAK DIGITAL ID (Left Card) */
        .profile-side-card { background: linear-gradient(135deg, var(--card-bg) 40%, rgba(30,58,138,0.05) 100%); border-radius: 24px; border: 1px solid var(--border-subtle); padding: 0; box-shadow: var(--shadow-sm); text-align: center; position: relative; overflow: hidden; }
        .profile-id-header { background: linear-gradient(90deg, #0f172a 0%, #1e3a8a 100%); padding: 30px 20px 60px 20px; color: white; position: relative; }
        .kavak-logo-watermark { position: absolute; top: -10px; right: -20px; width: 120px; opacity: 0.1; }
        
        .avatar-wrapper { position: relative; display: inline-block; margin-top: -50px; margin-bottom: 20px; z-index: 5; }
        .avatar-lg { width: 140px; height: 140px; border-radius: 50%; object-fit: cover; border: 6px solid var(--card-bg); box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: var(--card-bg);}
        .btn-change-photo { position: absolute; bottom: 5px; right: 5px; background: #D97706; color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; border: 3px solid var(--card-bg); transition: 0.2s; display:none; box-shadow: 0 2px 5px rgba(0,0,0,0.2);} 
        .btn-change-photo:hover { transform: scale(1.1); background: #B45309; }

        .side-name { font-size: 22px; font-weight: 800; color: var(--text-main); margin: 0 0 5px 0; letter-spacing: -0.5px;}
        .side-role { font-size: 13px; font-weight: 800; color: var(--accent-color); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 25px; display: inline-block; background: rgba(30,58,138,0.1); padding: 4px 12px; border-radius: 20px;}
        
        .profile-side-body { padding: 0 30px 30px 30px; }
        
        .side-info-block { text-align: left; padding: 12px 15px; background: var(--input-bg); border-radius: 12px; margin-bottom: 12px; border: 1px solid transparent; transition: 0.2s; }
        .side-info-block:hover { border-color: var(--border-subtle); background: var(--card-bg); }
        .side-info-label { display: block; font-size: 10px; font-weight: 800; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.3px;}
        .side-info-value { font-size: 14px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 10px;}
        .side-info-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.05); }

        /* RIGHT FORM CARD */
        .profile-form-card { background: var(--card-bg); border-radius: 24px; border: 1px solid var(--border-subtle); padding: 40px; box-shadow: var(--shadow-sm); }
        .form-section-title { font-size: 15px; font-weight: 800; color: var(--text-main); margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-section-title i { color: var(--accent-color); font-size: 18px; }
        
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;}
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;}
        .form-row-address { display: grid; grid-template-columns: 3fr 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-row-emergency { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        
        @media (max-width: 768px) { 
            .form-row-2, .form-row-3, .form-row-address, .form-row-emergency { grid-template-columns: 1fr; } 
        }

        .form-group { width: 100%; }
        .form-label { display: block; margin-bottom: 6px; font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;}
        
        .form-input { width: 100%; padding: 12px 0; border: none; border-bottom: 2px solid transparent; font-size: 14px; font-weight: 600; background-color: transparent; color: var(--text-main); transition: 0.3s; font-family: 'Inter', sans-serif; outline: none; border-radius: 0;}
        .form-input:read-only { color: var(--text-main); opacity: 1; cursor: default; }
        .form-input.is-editing { border-bottom: 2px solid var(--border-subtle); padding: 12px 10px; background-color: var(--input-bg); border-radius: 6px; font-weight: 500;}
        .form-input.is-editing:focus { border-bottom-color: var(--accent-color); background-color: var(--card-bg); }
        select.form-input.is-editing { cursor: pointer; }
        select.form-input:disabled { appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: none; opacity: 1; color: var(--text-main); }

        .form-divider { height: 1px; background: var(--border-subtle); margin: 35px 0; }
        .btn-save-bottom { background: var(--accent-color); color: white; border: none; padding: 14px 30px; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; transition: 0.2s; display: none; width: 100%; justify-content: center; align-items: center; gap: 10px; margin-top: 30px;}
        .btn-save-bottom:hover { background: var(--corporate-blue); box-shadow: var(--shadow-md); transform: translateY(-2px); }
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Mi Perfil';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div class="expediente-header">
                    <div class="expediente-title">
                        <h1>Expediente de Empleado</h1>
                        <p>Visualiza y gestiona tu información corporativa.</p>
                    </div>
                    <button type="button" class="btn-edit-info" id="btnEnableEdit"><i class="fas fa-pen"></i> Editar Información</button>
                </div>

                <form action="index.php?action=update_profile" method="POST" enctype="multipart/form-data" id="profileForm">
                    <div class="expediente-grid">
                        
                        <div class="profile-side-card">
                            <div class="profile-id-header">
                                <i class="fas fa-id-badge" style="font-size:30px; opacity:0.2; position:absolute; top:20px; left:20px;"></i>
                                <img src="<?php echo BASE_URL; ?>assets/img/LogoLetraBlanca.png" class="kavak-logo-watermark">
                                <div style="font-size:10px; font-weight:800; letter-spacing:1px; text-transform:uppercase; margin-bottom:5px; opacity:0.8;">Kavak ID</div>
                                <div style="font-weight:600; font-size:14px;"><?php echo htmlspecialchars($currentUser['rut']); ?></div>
                            </div>
                            
                            <div class="profile-side-body">
                                <div class="avatar-wrapper">
                                    <img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo htmlspecialchars($foto_perfil); ?>" class="avatar-lg" id="avatarPreview">
                                    <label for="avatarUpload" class="btn-change-photo" title="Cambiar Foto de Perfil" id="btnChangePhoto"><i class="fas fa-camera"></i></label>
                                    <input type="file" name="foto_perfil" id="avatarUpload" style="display:none;" accept="image/png, image/jpeg, image/webp">
                                </div>
                                <h2 class="side-name"><?php echo htmlspecialchars($currentUser['nombre'] . ' ' . $currentUser['apellido']); ?></h2>
                                <span class="side-role"><?php echo htmlspecialchars($currentUser['cargo_nombre'] ?? 'Sin Cargo'); ?></span>
                                
                                <div class="side-info-block">
                                    <span class="side-info-label">Hub / Ubicación</span>
                                    <span class="side-info-value">
                                        <div class="side-info-icon"><i class="fas fa-map-marker-alt" style="color:#D97706;"></i></div> 
                                        <?php echo htmlspecialchars($currentUser['sucursal_nombre'] ?? 'Sin Asignar'); ?>
                                    </span>
                                </div>
                                
                                <div class="side-info-block">
                                    <span class="side-info-label">Cumpleaños</span>
                                    <span class="side-info-value">
                                        <div class="side-info-icon"><i class="fas fa-birthday-cake" style="color:#10B981;"></i></div> 
                                        <?php echo !empty($currentUser['fecha_nacimiento']) ? date('d \d\e F', strtotime($currentUser['fecha_nacimiento'])) : 'No registrado'; ?>
                                    </span>
                                </div>

                                <div class="side-info-block">
                                    <span class="side-info-label">Fecha de Ingreso</span>
                                    <span class="side-info-value">
                                        <div class="side-info-icon"><i class="fas fa-calendar-check" style="color:var(--accent-color);"></i></div> 
                                        <?php echo $currentUser['fecha_ingreso'] ? date('d M, Y', strtotime($currentUser['fecha_ingreso'])) : '-'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="profile-form-card">
                            <h3 class="form-section-title"><i class="fas fa-address-book"></i> Datos de Contacto</h3>
                            <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($currentUser['nombre']); ?>">
                            <input type="hidden" name="apellido" value="<?php echo htmlspecialchars($currentUser['apellido']); ?>">

                            <div class="form-row-2">
                                <div class="form-group">
                                    <label class="form-label">Celular Personal *</label>
                                    <input type="text" name="telefono" id="phoneInput" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['telefono'] ?? ''); ?>" placeholder="+56 9 XXXX XXXX" readonly required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Correo Personal</label>
                                    <input type="email" name="correo_personal" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['correo_personal'] ?? ''); ?>" placeholder="ejemplo@gmail.com" readonly>
                                </div>
                            </div>
                            <div class="form-row-2">
                                <div class="form-group">
                                    <label class="form-label">Apodo o "¿Cómo te decimos?"</label>
                                    <input type="text" name="apodo" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['apodo'] ?? ''); ?>" placeholder="Ej: Pato, Nacho" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Enlace a LinkedIn</label>
                                    <input type="url" name="linkedin" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/..." readonly>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <h3 class="form-section-title"><i class="fas fa-home"></i> Dirección Particular</h3>
                            
                            <div class="form-row-address">
                                <div class="form-group">
                                    <label class="form-label">Calle / Avenida</label>
                                    <input type="text" name="calle" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['calle'] ?? ''); ?>" placeholder="Ej: Av. Providencia" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Numeración</label>
                                    <input type="text" name="numeracion" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['numeracion'] ?? ''); ?>" placeholder="Ej: 1234" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Depto / Casa</label>
                                    <input type="text" name="depto" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['depto'] ?? ''); ?>" placeholder="Ej: 402" readonly>
                                </div>
                            </div>

                            <div class="form-row-3">
                                <div class="form-group">
                                    <label class="form-label">Región</label>
                                    <select name="region_id" id="regionSelector" class="form-input editable-field" disabled>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($regiones as $reg): ?>
                                            <option value="<?php echo $reg['id']; ?>" <?php if (($currentUser['region_id'] ?? '') == $reg['id'])
        echo 'selected'; ?>><?php echo htmlspecialchars($reg['nombre']); ?></option>
                                        <?php
endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Provincia</label>
                                    <select name="provincia_id" id="provinciaSelector" class="form-input editable-field" disabled>
                                        <option value="">Seleccione...</option>
                                        <?php if (!empty($provincias)):
    foreach ($provincias as $prov): ?>
                                            <option value="<?php echo $prov['id']; ?>" <?php if (($currentUser['provincia_id'] ?? '') == $prov['id'])
            echo 'selected'; ?>><?php echo htmlspecialchars($prov['nombre']); ?></option>
                                        <?php
    endforeach;
endif; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Comuna</label>
                                    <select name="comuna_id" id="comunaSelector" class="form-input editable-field" disabled>
                                        <option value="">Seleccione...</option>
                                        <?php if (!empty($comunas)):
    foreach ($comunas as $com): ?>
                                            <option value="<?php echo $com['id']; ?>" <?php if (($currentUser['comuna_id'] ?? '') == $com['id'])
            echo 'selected'; ?>><?php echo htmlspecialchars($com['nombre']); ?></option>
                                        <?php
    endforeach;
endif; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-divider"></div>

                            <h3 class="form-section-title"><i class="fas fa-heartbeat" style="color:#EF4444;"></i> Contacto de Emergencia</h3>
                            
                            <div class="form-row-emergency">
                                <div class="form-group">
                                    <label class="form-label">Nombre Completo del Contacto</label>
                                    <input type="text" name="emergencia_nombre" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['emergencia_nombre'] ?? ''); ?>" placeholder="Ej: María Pérez" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Parentesco</label>
                                    <input type="text" name="emergencia_parentesco" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['emergencia_parentesco'] ?? ''); ?>" placeholder="Ej: Madre, Esposo" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Teléfono de Emergencia</label>
                                    <input type="text" name="emergencia_telefono" class="form-input editable-field" value="<?php echo htmlspecialchars($currentUser['emergencia_telefono'] ?? ''); ?>" placeholder="+56 9..." readonly>
                                </div>
                            </div>

                            <button type="submit" class="btn-save-bottom" id="btnSaveProfile"><i class="fas fa-save"></i> Actualizar Expediente</button>

                            <div class="form-divider"></div>

                            <h3 class="form-section-title"><i class="fas fa-star" style="color:#F59E0B;"></i> Reconocimientos (Kudos)</h3>
                            <?php if (empty($kudosRecibidos)): ?>
                                <p style="font-size:13px; color:var(--text-secondary);"><i class="fas fa-info-circle"></i> Aún no has recibido reconocimientos. ¡Sigue brillando!</p>
                            <?php
else: ?>
                                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:15px;">
                                    <?php foreach ($kudosRecibidos as $kudo):
        $fotoKudo = $kudo['emisor_foto'] ? '<?php echo BASE_URL; ?>assets/uploads/profiles/' . $kudo['emisor_foto'] : '<?php echo BASE_URL; ?>assets/uploads/profiles/default.png';
?>
                                    <div style="background:rgba(245,158,11,0.05); border:1px solid rgba(245,158,11,0.2); border-radius:12px; padding:15px; display:flex; gap:15px;">
                                        <div style="font-size:24px; color:#F59E0B;"><i class="fas fa-<?php echo htmlspecialchars($kudo['insignia']); ?>"></i></div>
                                        <div>
                                            <div style="font-size:13px; color:var(--text-main); font-weight:600; margin-bottom:5px;">"<?php echo htmlspecialchars($kudo['motivo']); ?>"</div>
                                            <div style="display:flex; align-items:center; gap:8px;">
                                                <img src="<?php echo $fotoKudo; ?>" style="width:20px; height:20px; border-radius:50%; object-fit:cover;">
                                                <span style="font-size:11px; color:var(--text-secondary); font-weight:700;">De: <?php echo htmlspecialchars($kudo['emisor_nombre'] . ' ' . $kudo['emisor_apellido']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
    endforeach; ?>
                                </div>
                            <?php
endif; ?>
                            
                        </div>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle'); const themeIcon = document.getElementById('themeIcon'); const themeText = document.getElementById('themeText'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') { body.classList.add('dark-mode'); if(themeIcon) themeIcon.className = 'fas fa-sun'; if(themeText) themeText.textContent = 'Modo Claro'; }
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); const isDark = body.classList.contains('dark-mode'); localStorage.setItem('theme', isDark ? 'dark' : 'light'); if(themeIcon) themeIcon.className = isDark ? 'fas fa-sun' : 'fas fa-moon'; if(themeText) themeText.textContent = isDark ? 'Modo Claro' : 'Modo Oscuro'; });
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        document.getElementById('avatarUpload').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) { document.getElementById('avatarPreview').src = e.target.result; }
                reader.readAsDataURL(e.target.files[0]);
                document.getElementById('btnSaveProfile').style.display = 'flex';
            }
        });

        document.getElementById('btnEnableEdit').addEventListener('click', function() {
            document.querySelectorAll('.editable-field').forEach(el => {
                el.readOnly = false;
                el.classList.add('is-editing');
            });
            document.querySelectorAll('select.editable-field').forEach(el => {
                el.disabled = false;
                el.classList.add('is-editing');
            });
            document.getElementById('btnChangePhoto').style.display = 'flex';
            this.style.display = 'none';
            document.getElementById('btnSaveProfile').style.display = 'flex';
            document.getElementById('phoneInput').focus();
        });

        document.getElementById('profileForm').addEventListener('submit', function() {
            document.querySelectorAll('select.editable-field').forEach(el => el.disabled = false);
        });

        const phoneInput = document.getElementById('phoneInput');
        phoneInput.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, ''); 
            if (x.startsWith('569')) { x = x.substring(3); } 
            else if (x.startsWith('56')) { x = x.substring(2); } 
            else if (x.startsWith('9')) { x = x.substring(1); }
            if (x.length > 8) { x = x.substring(0, 8); } 
            if (x.length === 0) { e.target.value = ''; } 
            else if (x.length <= 4) { e.target.value = '+56 9 ' + x; } 
            else { e.target.value = '+56 9 ' + x.substring(0, 4) + ' ' + x.substring(4, 8); }
        });

        document.getElementById('regionSelector').addEventListener('change', function() {
            const regionId = this.value;
            const provinciaSelector = document.getElementById('provinciaSelector');
            const comunaSelector = document.getElementById('comunaSelector');
            provinciaSelector.innerHTML = '<option value="">Cargando...</option>';
            provinciaSelector.disabled = true;
            comunaSelector.innerHTML = '<option value="">Seleccione Provincia primero</option>';
            comunaSelector.disabled = true;

            if (regionId) {
                fetch(`index.php?action=ajax_get_provincias&region_id=${regionId}`)
                    .then(response => response.json())
                    .then(data => {
                        provinciaSelector.innerHTML = '<option value="">Seleccione Provincia...</option>';
                        data.forEach(prov => { provinciaSelector.innerHTML += `<option value="${prov.id}">${prov.nombre}</option>`; });
                        provinciaSelector.disabled = false;
                        provinciaSelector.classList.add('is-editing');
                    });
            } else { provinciaSelector.innerHTML = '<option value="">Seleccione...</option>'; }
        });

        document.getElementById('provinciaSelector').addEventListener('change', function() {
            const provinciaId = this.value;
            const comunaSelector = document.getElementById('comunaSelector');
            comunaSelector.innerHTML = '<option value="">Cargando...</option>';
            comunaSelector.disabled = true;

            if (provinciaId) {
                fetch(`index.php?action=ajax_get_comunas&provincia_id=${provinciaId}`)
                    .then(response => response.json())
                    .then(data => {
                        comunaSelector.innerHTML = '<option value="">Seleccione Comuna...</option>';
                        data.forEach(comuna => { comunaSelector.innerHTML += `<option value="${comuna.id}">${comuna.nombre}</option>`; });
                        comunaSelector.disabled = false;
                        comunaSelector.classList.add('is-editing');
                    });
            } else { comunaSelector.innerHTML = '<option value="">Seleccione...</option>'; }
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