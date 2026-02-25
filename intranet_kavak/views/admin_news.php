<?php
global $globalQuickLinks;
if (empty($_SESSION['p_noticias'])) {
    die("Acceso denegado.");
}
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/News.php';
require_once 'models/QuickLink.php';

$db = (new Database())->getConnection();

$newsModel = new News($db);
$noticias = $newsModel->getAll();
$quickLinksList = (new QuickLink($db))->getAll();

$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Comunicaciones - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
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

        .data-grid-container { background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); overflow: hidden; margin-top: 20px; }
        .data-table { width: 100%; border-collapse: collapse; text-align: left; }
        .data-table th { background: var(--input-bg); padding: 18px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-secondary); border-bottom: 2px solid var(--border-subtle); letter-spacing: 0.5px; }
        .data-table td { padding: 18px 25px; border-bottom: 1px solid var(--border-subtle); font-size: 14px; color: var(--text-main); vertical-align: middle; transition: background 0.2s;}
        .data-table tr:hover td { background: rgba(37,99,235,0.02); }
        .data-table tr:last-child td { border-bottom: none; }
        
        .badge-news { padding: 6px 12px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase; display: inline-block;}
        .type-novedad { background: rgba(37, 99, 235, 0.1); color: var(--accent-color); border: 1px solid rgba(37,99,235,0.2); }
        .type-evento { background: rgba(124, 58, 237, 0.1); color: #7C3AED; border: 1px solid rgba(124,58,237,0.2); }
        .type-curso { background: rgba(217, 119, 6, 0.1); color: #D97706; border: 1px solid rgba(217,119,6,0.2); }

        .action-btns { display: flex; gap: 8px; }
        .btn-action { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-subtle); background: var(--input-bg); color: var(--text-secondary); cursor: pointer; transition: 0.2s; text-decoration: none; }
        .btn-action:hover { border-color: var(--accent-color); color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm); background: var(--card-bg);}
        .btn-delete:hover { border-color: #EF4444; color: #EF4444; background: rgba(239,68,68,0.05); }

        /* Modal */
        .modal-news { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.85); z-index: 5000; backdrop-filter: blur(8px); justify-content: center; align-items: center; padding: 20px; }
        .modal-content-news { background: var(--card-bg); width: 100%; max-width: 800px; height: 90vh; border-radius: 24px; display: flex; flex-direction: column; overflow: hidden; position: relative; animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: var(--shadow-xl); border: 1px solid var(--border-subtle);}
        @keyframes popIn { from { transform: scale(0.95) translateY(20px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }
        
        .modal-header { padding: 25px 30px; border-bottom: 1px solid var(--border-subtle); background: var(--input-bg); display: flex; justify-content: space-between; align-items: center;}
        .modal-title { font-size: 20px; font-weight: 800; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 10px;}
        .btn-close-modal { background: transparent; border: none; font-size: 20px; color: var(--text-secondary); cursor: pointer; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
        .btn-close-modal:hover { background: rgba(239,68,68,0.1); color: #EF4444; transform: rotate(90deg); }

        .modal-scroll { flex: 1; overflow-y: auto; padding: 30px; }
        
        .form-grid-modal { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 11px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;}
        .form-input { width: 100%; padding: 12px 16px; border: 2px solid var(--border-subtle); border-radius: 10px; background: var(--input-bg); color: var(--text-main); font-size: 14px; font-family: 'Inter', sans-serif; transition: 0.3s; outline: none;}
        .form-input:focus { border-color: var(--accent-color); background: var(--card-bg); box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
        
        .ai-toolbar { display: flex; gap: 10px; margin-bottom: 0; background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(37,99,235,0.1)); padding: 15px; border-radius: 12px 12px 0 0; border: 2px solid rgba(124,58,237,0.2); border-bottom: none; overflow-x: auto; align-items: center;}
        .ai-label { font-size: 11px; text-transform: uppercase; font-weight: 800; color: #7C3AED; margin-right: 10px; display: flex; align-items: center; gap: 6px;}
        .btn-ai { background: white; border: 1px solid rgba(124,58,237,0.3); padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 700; color: #7C3AED; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; box-shadow: 0 2px 4px rgba(124,58,237,0.1);}
        .btn-ai:hover { background: #7C3AED; color: white; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(124,58,237,0.3);}
        
        /* ESTILOS ESPECÍFICOS PARA QUILL (EDITOR) */
        /* Corrección crítica: Se define altura y color de fondo explícitos */
        #quill-news { height: 300px; background: white; color: #333; font-family: 'Inter', sans-serif; font-size: 15px; }
        .ql-toolbar.ql-snow { border: 2px solid var(--border-subtle); background: var(--input-bg); border-top: 1px solid rgba(124,58,237,0.2); border-radius: 0; }
        .ql-container.ql-snow { border: 2px solid var(--border-subtle); border-top: none; border-radius: 0 0 12px 12px; }
        
        .btn-submit-modal { width: 100%; padding: 16px; background: var(--accent-color); color: white; border: none; border-radius: 12px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: var(--shadow-sm); display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px;}
        .btn-submit-modal:hover { background: var(--corporate-blue); transform: translateY(-2px); box-shadow: var(--shadow-md); }
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Panel de Control - Noticias';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 25px;">
                    <div>
                        <h2 style="font-weight:800; color:var(--text-main); margin:0; font-size:26px;">Gestión de Comunicaciones</h2>
                        <p style="color:var(--text-secondary); margin:5px 0 0 0;">Administra las noticias, eventos y capacitaciones del muro principal.</p>
                    </div>
                    <div style="display:flex; gap: 15px; align-items:center;">
                        <input type="text" id="newsSearch" placeholder="Buscar novedades, eventos..." autocomplete="off" style="padding:10px; border-radius:8px; border:1px solid var(--border-subtle); background:var(--input-bg); color:var(--text-main); font-size:14px; width:220px;">
                        <button onclick="openModal('create')" class="btn-login" style="padding:12px 24px;"><i class="fas fa-plus-circle"></i> Nueva Publicación</button>
                    </div>
                </div>

                <?php if (isset($_GET['success'])): ?><div class="success-msg" style="background:#DCFCE7; color:#166534; padding:15px; border-radius:10px; margin-bottom:20px; border:1px solid #4ADE80;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div><?php
endif; ?>

                <div class="data-grid-container">
                    <table class="data-table" id="newsTable">
                        <thead>
                            <tr>
                                <th>Título del Comunicado</th>
                                <th>Tipo</th>
                                <th>Autor</th>
                                <th>Fecha Publicación</th>
                                <th style="text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($noticias as $n):
    $class = 'type-novedad';
    if ($n['tipo'] == 'Evento Importante')
        $class = 'type-evento';
    if ($n['tipo'] == 'Curso Pendiente')
        $class = 'type-curso';
    $searchString = strtolower($n['titulo'] . ' ' . $n['tipo'] . ' ' . $n['autor_nombre']);
?>
                            <tr class="news-row" data-search="<?php echo $searchString; ?>">
                                <td style="font-weight:700; color:var(--text-main);"><?php echo htmlspecialchars($n['titulo']); ?></td>
                                <td><span class="badge-news <?php echo $class; ?>"><?php echo $n['tipo']; ?></span></td>
                                <td><div style="display:flex; align-items:center; gap:8px;"><img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo $n['foto_perfil'] ?? 'default.png'; ?>" style="width:24px; height:24px; border-radius:50%;"> <?php echo htmlspecialchars($n['autor_nombre']); ?></div></td>
                                <td style="color:var(--text-secondary); font-size:13px;"><?php echo date('d/m/Y H:i', strtotime($n['creado_en'])); ?></td>
                                <td style="text-align:right;">
                                    <div class="action-btns" style="justify-content:flex-end;">
                                        <button class="btn-action" title="Editar" onclick='openEditModal(<?php echo json_encode($n, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="fas fa-pen"></i></button>
                                        <a href="index.php?action=delete_news&id=<?php echo $n['id']; ?>" class="btn-action btn-delete" onclick="return confirm('¿Eliminar esta publicación?')"><i class="fas fa-trash-alt"></i></a>
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

    <div id="newsModal" class="modal-news">
        <div class="modal-content-news">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle"><i class="fas fa-pen-square" style="color:var(--accent-color);"></i> Publicar Noticia</h3>
                <button onclick="closeModal()" class="btn-close-modal"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="modal-scroll">
                <form id="newsForm" action="index.php?action=create_news" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="news_id">
                    
                    <div class="form-group">
                        <label class="form-label">Título de la Publicación *</label>
                        <input type="text" name="titulo" id="news_titulo" class="form-input" required placeholder="Ej: Nueva política de vacaciones 2026">
                    </div>

                    <div class="form-grid-modal">
                        <div class="form-group">
                            <label class="form-label">Tipo de Contenido</label>
                            <select name="tipo" id="news_tipo" class="form-input" onchange="toggleCourseFields()">
                                <option value="Novedad">Novedad / Noticia</option>
                                <option value="Evento Importante">Evento Importante</option>
                                <option value="Curso Pendiente">Capacitación (Curso)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fecha Límite / Evento (Opcional)</label>
                            <input type="date" name="fecha_vencimiento" id="news_fecha" class="form-input">
                        </div>
                    </div>

                    <div id="courseFields" style="display:none;" class="form-group">
                        <label class="form-label">Enlace al Curso / Plataforma</label>
                        <input type="url" name="curso_link" id="news_link" class="form-input" placeholder="https://...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Imagen de Portada</label>
                        <input type="file" name="curso_imagen" class="form-input" accept="image/*">
                        <p id="edit_img_note" style="font-size:11px; color:var(--text-secondary); margin-top:5px; display:none; font-style:italic;"><i class="fas fa-info-circle"></i> Si estás editando, deja esto vacío para mantener la imagen actual.</p>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Cuerpo del Comunicado *</label>
                        
                        <div class="ai-toolbar">
                            <div class="ai-label"><i class="fas fa-robot"></i> Asistente IA:</div>
                            <button type="button" class="btn-ai" onclick="callAI('improve')"><i class="fas fa-spell-check"></i> Mejorar Ortografía</button>
                            <button type="button" class="btn-ai" onclick="callAI('professional')"><i class="fas fa-user-tie"></i> Tono Formal</button>
                            <button type="button" class="btn-ai" onclick="callAI('friendly')"><i class="fas fa-smile"></i> Tono Amigable</button>
                        </div>

                        <div id="quill-news" style="height: 300px; background:white;"></div>
                        <input type="hidden" name="contenido" id="news_contenido">
                    </div>

                    <button type="submit" class="btn-submit-modal" id="btnSubmitNews"><i class="fas fa-paper-plane"></i> Publicar Ahora</button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($isSuperAdmin): ?>
    <div id="modalAddQuickLink" class="modal-news" style="z-index: 6000;">
        <div class="modal-content-news" style="max-width: 400px; height: auto;">
            <div class="modal-header">
                <h3 class="modal-title" style="font-size:18px;"><i class="fas fa-link"></i> Nuevo Acceso Directo</h3>
                <button onclick="document.getElementById('modalAddQuickLink').style.display='none'" class="btn-close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-scroll" style="padding: 30px;">
                <form action="index.php?action=create_quicklink" method="POST">
                    <div class="form-group"><label class="form-label">Nombre</label><input type="text" name="nombre" class="form-input" required placeholder="Ej: Buk"></div>
                    <div class="form-group"><label class="form-label">URL</label><input type="url" name="url" class="form-input" required placeholder="https://..."></div>
                    <div class="form-group"><label class="form-label">Icono (FontAwesome)</label><input type="text" name="icono" class="form-input" required placeholder="fas fa-star"></div>
                    <button type="submit" class="btn-submit-modal"><i class="fas fa-save"></i> Guardar</button>
                </form>
            </div>
        </div>
    </div>
    <?php
endif; ?>

    <script>
        const themeToggle = document.getElementById('themeToggle'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') body.classList.add('dark-mode');
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light'); });
        document.getElementById('openSidebar').addEventListener('click', () => { document.querySelector('.sidebar').classList.add('active'); });
        
        // Inicialización de Quill Editor
        var quill;
        document.addEventListener('DOMContentLoaded', function() {
            quill = new Quill('#quill-news', {
                theme: 'snow',
                placeholder: 'Escribe aquí el contenido...',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });
        });

        // Buscador
        const searchInput = document.getElementById('newsSearch');
        if(searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                document.querySelectorAll('.news-row').forEach(row => {
                    row.style.display = row.getAttribute('data-search').includes(term) ? '' : 'none';
                });
            });
        }

        // Lógica Modal
        function openModal(mode) {
            document.getElementById('newsModal').style.display = 'flex';
            if(mode === 'create') {
                document.getElementById('modalTitle').innerHTML = '<i class="fas fa-pen-square" style="color:var(--accent-color);"></i> Publicar Noticia';
                document.getElementById('btnSubmitNews').innerHTML = '<i class="fas fa-paper-plane"></i> Publicar Ahora';
                document.getElementById('newsForm').action = 'index.php?action=create_news';
                document.getElementById('newsForm').reset();
                document.getElementById('news_id').value = '';
                document.getElementById('edit_img_note').style.display = 'none';
                if(quill) quill.setContents([]);
            }
        }

        function openEditModal(data) {
            document.getElementById('newsModal').style.display = 'flex';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit" style="color:#D97706;"></i> Editar Publicación';
            document.getElementById('btnSubmitNews').innerHTML = '<i class="fas fa-sync-alt"></i> Guardar Cambios';
            document.getElementById('newsForm').action = 'index.php?action=update_news';
            
            document.getElementById('news_id').value = data.id;
            document.getElementById('news_titulo').value = data.titulo;
            document.getElementById('news_tipo').value = data.tipo;
            document.getElementById('news_fecha').value = data.fecha_vencimiento ? data.fecha_vencimiento.split(' ')[0] : '';
            document.getElementById('news_link').value = data.curso_link || '';
            document.getElementById('edit_img_note').style.display = 'block';
            
            if(quill) quill.root.innerHTML = data.contenido;
            toggleCourseFields();
        }

        function closeModal() { document.getElementById('newsModal').style.display = 'none'; }

        function toggleCourseFields() {
            const val = document.getElementById('news_tipo').value;
            document.getElementById('courseFields').style.display = (val === 'Curso Pendiente') ? 'block' : 'none';
        }

        document.getElementById('newsForm').onsubmit = function() {
            document.getElementById('news_contenido').value = quill.root.innerHTML;
        };

        // Función IA
        async function callAI(action) {
            let range = quill.getSelection();
            let text = range && range.length > 0 ? quill.getText(range.index, range.length) : quill.getText();
            
            if(text.trim().length < 10) return alert("Escribe o selecciona más texto para que la IA pueda trabajar.");
            
            quill.disable();
            const originalPlaceholder = quill.root.dataset.placeholder;
            quill.root.dataset.placeholder = "✨ Gemini está pensando...";

            try {
                const res = await fetch('index.php?action=ajax_ai', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `ai_action=${action}&text=${encodeURIComponent(text)}`
                });
                const data = await res.json();
                
                if(data.success) { 
                    if(range && range.length > 0) {
                        quill.deleteText(range.index, range.length);
                        quill.insertText(range.index, data.result);
                    } else {
                        quill.setText(data.result);
                    }
                } else { 
                    alert("Error IA: " + (data.error || "Desconocido")); 
                }
            } catch(e) { 
                alert("Error de red al contactar con la IA."); 
            }
            quill.enable();
            quill.root.dataset.placeholder = originalPlaceholder;
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