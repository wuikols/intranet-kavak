<?php
// =================================================================================
// VISTA: WIKI Y GESTIÓN DEL CONOCIMIENTO (CORREGIDO)
// =================================================================================
global $globalQuickLinks;
if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}

require_once 'config/database.php';

require_once 'models/User.php';

require_once 'models/Wiki.php';

require_once 'models/QuickLink.php';

$db = (new Database())->getConnection();

$wikiModel = new Wiki($db);
$quickLinksList = (new QuickLink($db))->getAll();

// Obtener datos
$categories = $wikiModel->getCategories();
$articles = $wikiModel->getAll();

$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isAdmin = strpos($rol_nombre, 'ADMIN') !== false || strpos($rol_nombre, 'SUPER') !== false || strpos($rol_nombre, 'RRHH') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Wiki y Procesos - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.5/purify.min.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Estilos Base Sidebar */
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }
        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
        .ql-admin-add { background: rgba(37,99,235,0.1); color: var(--accent-color); border: 1px dashed var(--accent-color); }

        /* WIKI STYLES */
        .wiki-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        
        /* CORRECCIÓN GRID: align-items: start evita que se estiren */
        .wiki-grid { display: grid; grid-template-columns: 250px 1fr; gap: 30px; align-items: start; height: calc(100vh - 180px); overflow: hidden; }
        @media(max-width: 900px) { .wiki-grid { grid-template-columns: 1fr; height: auto; overflow: visible; } }

        /* Categorías Lateral */
        .cat-card-wrapper { background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-subtle); padding: 20px; height: 100%; overflow-y: auto; }
        .cat-list { list-style: none; padding: 0; margin: 0; }
        .cat-item { padding: 8px 12px; border-radius: 8px; cursor: pointer; color: var(--text-secondary); font-weight: 600; font-size: 13px; transition: 0.2s; display: flex; align-items: center; gap: 8px; margin-bottom: 4px; justify-content: space-between; }
        .cat-item:hover, .cat-item.active { background: var(--card-bg); color: var(--accent-color); box-shadow: var(--shadow-sm); }
        .cat-del-btn { color: #EF4444; opacity: 0; transition: 0.2s; padding: 3px; font-size: 12px; }
        .cat-item:hover .cat-del-btn { opacity: 1; }

        /* Artículos Grid */
        .articles-scroller { height: 100%; overflow-y: auto; padding-right: 15px; }
        .articles-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
        .article-card { background: var(--card-bg); border: 1px solid var(--border-subtle); border-radius: 16px; overflow: hidden; transition: 0.3s; cursor: pointer; display: flex; flex-direction: column; height: auto; position: relative; }
        .article-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-md); border-color: var(--accent-color); }
        
        .art-cover { height: 140px; background: var(--input-bg); object-fit: cover; width: 100%; border-bottom: 1px solid var(--border-subtle); }
        .art-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .art-cat { font-size: 10px; text-transform: uppercase; font-weight: 800; color: var(--accent-color); margin-bottom: 5px; }
        .art-title { font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0 0 10px 0; line-height: 1.4; }
        .art-meta { margin-top: auto; font-size: 12px; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed var(--border-subtle); padding-top: 10px; }
        
        .btn-delete-art { background: rgba(239, 68, 68, 0.1); color: #EF4444; border: none; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; opacity: 0; transition: 0.2s; }
        .article-card:hover .btn-delete-art { opacity: 1; }

        /* Modal Lectura */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.9); z-index: 5000; justify-content: center; align-items: center; backdrop-filter: blur(5px); padding: 20px; }
        .modal-content { background: var(--card-bg); width: 100%; max-width: 900px; height: 90vh; border-radius: 20px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; overflow: hidden; box-shadow: var(--shadow-xl); animation: zoomIn 0.3s ease; }
        @keyframes zoomIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        
        .mc-header { padding: 20px 30px; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--input-bg); }
        .mc-body { padding: 40px; overflow-y: auto; color: var(--text-main); line-height: 1.8; font-size: 15px; }
        .mc-body img { max-width: 100%; border-radius: 10px; margin: 20px 0; }
        
        /* Formulario Crear */
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 5px; font-size: 12px; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; }
        .form-input { width: 100%; padding: 12px; border: 1px solid var(--border-subtle); border-radius: 8px; background: var(--input-bg); color: var(--text-main); }
        .btn-submit { background: var(--accent-color); color: white; padding: 12px 20px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; }

        /* EDITOR IA & QUILL */
        #quill-editor { height: 300px; background: white; color: #333; font-family: 'Inter', sans-serif; }
        .ai-toolbar { display: flex; gap: 10px; margin-bottom: 0; background: linear-gradient(135deg, rgba(124,58,237,0.1), rgba(37,99,235,0.1)); padding: 10px; border-radius: 8px 8px 0 0; border: 1px solid rgba(124,58,237,0.2); align-items: center; }
        .btn-ai { background: white; border: 1px solid rgba(124,58,237,0.3); padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; color: #7C3AED; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .btn-ai:hover { background: #7C3AED; color: white; }
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Wiki Corporativa';
$topbarSearchId = 'wikiSearch';
$topbarSearchPlaceholder = 'Buscar procesos, manuales...';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <?php if (isset($_GET['success'])): ?><div class="success-msg" style="background:#DCFCE7; color:#166534; padding:15px; border-radius:10px; margin-bottom:20px; border:1px solid #4ADE80;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div><?php
endif; ?>

                <div class="wiki-header" style="display:flex; justify-content:flex-end;">
                    <button onclick="openCreateModal()" class="btn-submit" style="width:auto;"><i class="fas fa-plus"></i> Nuevo Artículo</button>
                </div>

                <div class="wiki-grid">
                    <div class="cat-card-wrapper">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0; color:var(--text-secondary); text-transform:uppercase; font-size:12px;">Categorías</h4>
                            <?php if ($isAdmin): ?>
                            <button onclick="document.getElementById('catModal').style.display='flex'" style="background:none; border:none; color:var(--accent-color); font-size:12px; cursor:pointer; font-weight:700;">+ Crear</button>
                            <?php
endif; ?>
                        </div>
                        
                        <ul class="cat-list">
                            <li class="cat-item active" onclick="filterWiki('all', this)">
                                <span><i class="fas fa-layer-group"></i> Todas</span>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li class="cat-item" onclick="filterWiki('<?php echo $cat['id']; ?>', this)">
                                    <span><i class="fas fa-folder"></i> <?php echo htmlspecialchars($cat['nombre']); ?></span>
                                    <?php if ($isAdmin): ?>
                                    <a href="index.php?action=delete_wiki_category&id=<?php echo $cat['id']; ?>" class="cat-del-btn" onclick="return confirm('¿Borrar categoría?')"><i class="fas fa-trash"></i></a>
                                    <?php
    endif; ?>
                                </li>
                            <?php
endforeach; ?>
                        </ul>
                    </div>

                    <div class="articles-scroller">
                        <div class="articles-container" id="articlesGrid">
                        <?php foreach ($articles as $art):
    $cover = $art['imagen_portada'] ? $art['imagen_portada'] : '<?php echo BASE_URL; ?>assets/img/wiki_default.jpg';
    $searchData = strtolower($art['titulo'] . ' ' . $art['contenido']);
?>
                        <div class="article-card" data-cat="<?php echo $art['categoria_id']; ?>" data-search="<?php echo htmlspecialchars($searchData, ENT_QUOTES); ?>" onclick="openArticle(<?php echo htmlspecialchars(json_encode($art), ENT_QUOTES, 'UTF-8'); ?>)">
                            <img src="<?php echo $cover; ?>" class="art-cover" onerror="this.src='<?php echo BASE_URL; ?>assets/img/wiki_default.jpg'">
                            <div class="art-body">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                                    <span class="art-cat" style="margin-bottom:0;"><?php echo htmlspecialchars($art['categoria_nombre']); ?></span>
                                    <?php if ($isAdmin): ?>
                                    <a class="btn-delete-art" href="index.php?action=delete_wiki_article&id=<?php echo $art['id']; ?>" onclick="event.stopPropagation(); return confirm('¿Borrar artículo?');" title="Borrar artículo"><i class="fas fa-trash"></i></a>
                                    <?php
    endif; ?>
                                </div>
                                <h3 class="art-title"><?php echo htmlspecialchars($art['titulo']); ?></h3>
                                <div class="art-meta">
                                    <span><i class="far fa-eye"></i> <?php echo $art['vistas']; ?></span>
                                    <span><?php echo date('d M Y', strtotime($art['creado_en'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="readModal" class="modal">
        <div class="modal-content">
            <div class="mc-header">
                <div>
                    <span id="readCat" style="font-size:11px; font-weight:800; color:var(--accent-color); text-transform:uppercase;">CATEGORÍA</span>
                    <h2 id="readTitle" style="margin:5px 0 0 0; font-size:24px;">Título del Artículo</h2>
                </div>
                <div style="display:flex; align-items:center; gap:15px;">
                    <?php if ($isAdmin): ?>
                    <a id="modalDeleteWikiBtn" href="#" style="background:none; border:none; color:#EF4444; cursor:pointer; text-decoration:none;" title="Eliminar artículo" onclick="return confirm('¿Borrar artículo?');"><i class="fas fa-trash fa-lg"></i></a>
                    <?php
endif; ?>
                    <button onclick="document.getElementById('readModal').style.display='none'" style="background:none; border:none; color:var(--text-secondary); font-size:24px; cursor:pointer;"><i class="fas fa-times"></i></button>
                </div>
            </div>
            <div class="mc-body" id="readBody">
                </div>
            <div class="mc-header" style="border-top:1px solid var(--border-subtle); border-bottom:none; justify-content:flex-start; gap:15px;">
                <a id="readDownload" href="#" target="_blank" class="btn-submit" style="width:auto; display:none;"><i class="fas fa-download"></i> Descargar Adjunto</a>
            </div>
        </div>
    </div>

    <div id="createModal" class="modal">
        <div class="modal-content" style="max-width:800px; height:90vh;">
            <div class="mc-header">
                <h3>Nuevo Documento</h3>
                <button onclick="document.getElementById('createModal').style.display='none'" style="background:none; border:none; color:var(--text-secondary); font-size:20px; cursor:pointer;"><i class="fas fa-times"></i></button>
            </div>
            <div class="mc-body" style="padding:20px; overflow-y:auto; flex:1;">
                <form id="createForm" action="index.php?action=create_wiki" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" class="form-input" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                            <?php
endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Contenido</label>
                        <div class="ai-toolbar">
                            <div style="font-size:11px; font-weight:800; color:#7C3AED; display:flex; align-items:center; gap:5px;"><i class="fas fa-robot"></i> ASISTENTE IA:</div>
                            <button type="button" class="btn-ai" onclick="callAI('improve')"><i class="fas fa-spell-check"></i> Ortografía</button>
                            <button type="button" class="btn-ai" onclick="callAI('professional')"><i class="fas fa-user-tie"></i> Formal</button>
                            <button type="button" class="btn-ai" onclick="callAI('expand')"><i class="fas fa-align-left"></i> Expandir</button>
                        </div>
                        <div id="quill-editor"></div>
                        <input type="hidden" name="contenido" id="hiddenContent">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                        <div class="form-group">
                            <label class="form-label">Portada (Img)</label>
                            <input type="file" name="imagen_portada" class="form-input" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Adjunto (PDF/DOC)</label>
                            <input type="file" name="archivo_adjunto" class="form-input">
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Publicar Documento</button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
    <div id="catModal" class="modal">
        <div class="modal-content" style="max-width:400px; height:auto;">
            <div class="mc-header">
                <h3>Nueva Categoría</h3>
                <button onclick="document.getElementById('catModal').style.display='none'" style="background:none; border:none; cursor:pointer;"><i class="fas fa-times"></i></button>
            </div>
            <div style="padding:20px;">
                <form action="index.php?action=create_wiki_category" method="POST">
                    <div class="form-group">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-input" required placeholder="Ej: Operaciones">
                    </div>
                    <button type="submit" class="btn-submit">Guardar</button>
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
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        // Quill Init
        var quill;
        function openCreateModal() {
            document.getElementById('createModal').style.display = 'flex';
            if(!quill) {
                quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    placeholder: 'Escribe el contenido del documento...',
                    modules: { toolbar: [['bold', 'italic', 'underline', 'strike'], [{'list':'ordered'},{'list':'bullet'}], [{'header':[1,2,3,false]}], ['link', 'clean']] }
                });
            }
        }

        // Submit Logic
        document.getElementById('createForm').onsubmit = function() {
            document.getElementById('hiddenContent').value = quill.root.innerHTML;
        };

        // AI Logic
        async function callAI(action) {
            let range = quill.getSelection();
            let text = range && range.length > 0 ? quill.getText(range.index, range.length) : quill.getText();
            if(text.trim().length < 5) return alert("Escribe algo más para que la IA pueda ayudarte.");
            
            quill.disable();
            try {
                const res = await fetch('index.php?action=ajax_ai', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `ai_action=${action}&text=${encodeURIComponent(text)}`
                });
                const data = await res.json();
                if(data.success) {
                    if(range && range.length > 0) { quill.deleteText(range.index, range.length); quill.insertText(range.index, data.result); }
                    else { quill.setText(data.result); }
                } else { alert("Error IA"); }
            } catch(e) { alert("Error de red"); }
            quill.enable();
        }

        // Filtering & Search
        function filterWiki(catId, btn) {
            document.querySelectorAll('.cat-item').forEach(i => i.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.article-card').forEach(card => {
                if(catId === 'all' || card.getAttribute('data-cat') == catId) card.style.display = 'flex';
                else card.style.display = 'none';
            });
        }

        document.getElementById('wikiSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.article-card').forEach(card => {
                const visible = card.getAttribute('data-search').includes(term);
                card.style.display = visible ? 'flex' : 'none';
            });
        });

        function openArticle(art) {
            document.getElementById('readTitle').innerText = art.titulo;
            document.getElementById('readCat').innerText = art.categoria_nombre;
            const modalDeleteBtn = document.getElementById('modalDeleteWikiBtn');
            if(modalDeleteBtn) {
                modalDeleteBtn.href = 'index.php?action=delete_wiki_article&id=' + art.id;
            }
            document.getElementById('readBody').innerHTML = DOMPurify.sanitize(art.contenido);
            const btnDown = document.getElementById('readDownload');
            if(art.archivo_adjunto) { btnDown.href = art.archivo_adjunto; btnDown.style.display = 'inline-flex'; } 
            else { btnDown.style.display = 'none'; }
            document.getElementById('readModal').style.display = 'flex';
            fetch('index.php?action=ajax_wiki_view', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'article_id='+art.id});
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