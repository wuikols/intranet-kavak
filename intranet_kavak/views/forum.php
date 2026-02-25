<?php
// =================================================================================
// VISTA: FORO Y COMUNIDAD (CORREGIDO Y POTENCIADO)
// =================================================================================
global $globalQuickLinks;
if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}

require_once 'config/database.php';

require_once 'models/User.php';

require_once 'models/Forum.php';

require_once 'models/QuickLink.php';

$db = (new Database())->getConnection();

$forumModel = new Forum($db);
$quickLinksList = (new QuickLink($db))->getAll();

$categories = $forumModel->getCategories();
$topics = $forumModel->getTopics();

$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
// Permisos de Administración
$isAdmin = strpos($rol_nombre, 'ADMIN') !== false || strpos($rol_nombre, 'SUPER') !== false || strpos($rol_nombre, 'RRHH') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Foro - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.5/purify.min.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Sidebar Styles (Consistentes) */
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }
        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
        .ql-admin-add { background: rgba(37,99,235,0.1); color: var(--accent-color); border: 1px dashed var(--accent-color); }

        /* FORUM LAYOUT CORREGIDO */
        .forum-layout { display: grid; grid-template-columns: 260px 1fr; gap: 30px; margin-top: 20px; align-items: start; height: calc(100vh - 180px); overflow: hidden;}
        @media(max-width: 900px) { .forum-layout { grid-template-columns: 1fr; height: auto; overflow: visible; } }
        
        .topics-scroller { overflow-y: auto; height: 100%; padding-right: 15px; }

        /* Categorías */
        .cat-card { background: var(--card-bg); border-radius: 16px; border: 1px solid var(--border-subtle); padding: 20px; height: 100%; overflow-y: auto; }
        .cat-btn { display: flex; align-items: center; justify-content: space-between; padding: 10px 15px; border-radius: 10px; cursor: pointer; color: var(--text-secondary); font-weight: 600; font-size: 13px; transition: 0.2s; margin-bottom: 5px; }
        .cat-btn:hover, .cat-btn.active { background: var(--input-bg); color: var(--accent-color); }
        
        .cat-del-btn { color: #EF4444; opacity: 0; transition: 0.2s; padding: 5px; font-size: 12px; }
        .cat-btn:hover .cat-del-btn { opacity: 1; }

        /* Topics Feed */
        .topic-card { background: var(--card-bg); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; margin-bottom: 20px; transition: 0.3s; cursor: pointer; position: relative; }
        .topic-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--accent-color); }
        
        .topic-header { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .topic-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .topic-meta { flex: 1; }
        .topic-author { font-weight: 800; color: var(--text-main); font-size: 14px; }
        .topic-date { font-size: 12px; color: var(--text-secondary); }
        .topic-cat-badge { background: rgba(37,99,235,0.1); color: var(--accent-color); padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 800; text-transform: uppercase; }

        .topic-title { font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 10px 0; }
        .topic-preview { font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        
        .topic-footer { display: flex; gap: 20px; border-top: 1px solid var(--border-subtle); padding-top: 15px; }
        .topic-stat { display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-secondary); font-weight: 600; }
        .topic-stat i { font-size: 16px; }
        .topic-stat.liked { color: #EF4444; }

        .btn-delete-topic { background: transparent; border: none; color: #EF4444; cursor: pointer; transition: 0.2s; font-size: 14px; padding: 5px; opacity: 0; }
        .topic-card:hover .btn-delete-topic { opacity: 1; }

        /* Modal Topic Full */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.9); z-index: 5000; justify-content: center; align-items: center; backdrop-filter: blur(5px); padding: 20px; }
        .modal-content { background: var(--card-bg); width: 100%; max-width: 800px; height: 90vh; border-radius: 20px; border: 1px solid var(--border-subtle); display: flex; flex-direction: column; overflow: hidden; animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        .mc-header { padding: 20px 25px; border-bottom: 1px solid var(--border-subtle); background: var(--input-bg); display: flex; justify-content: space-between; align-items: center; }
        .mc-scroll { flex: 1; overflow-y: auto; padding: 25px; }
        
        .comment-box { border-top: 1px solid var(--border-subtle); padding: 20px; background: var(--input-bg); display: flex; gap: 15px; align-items: flex-start; }
        .comment-input { flex: 1; background: var(--card-bg); border: 1px solid var(--border-subtle); border-radius: 12px; padding: 12px; color: var(--text-main); outline: none; resize: none; min-height: 45px; font-family: 'Inter', sans-serif; }
        .comment-input:focus { border-color: var(--accent-color); }
        .btn-send { background: var(--accent-color); color: white; border: none; width: 45px; height: 45px; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .btn-send:hover { transform: scale(1.05); }

        .comment-item { display: flex; gap: 15px; margin-bottom: 20px; }
        .comment-bubble { background: var(--input-bg); padding: 15px; border-radius: 0 16px 16px 16px; border: 1px solid var(--border-subtle); flex: 1; }
        .comment-meta { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; font-weight: 700; color: var(--text-secondary); }
        
        /* New Topic Modal Inputs */
        .form-input { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-subtle); background: var(--input-bg); color: var(--text-main); margin-bottom: 15px; }
        .btn-primary { background: var(--accent-color); color: white; padding: 12px 20px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; }

        /* EDITOR IA */
        #quill-editor { height: 200px; background: white; color: #333; font-family: 'Inter', sans-serif; border-radius: 0 0 8px 8px; }
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
$topbarTitle = 'Comunidad';
$topbarSearchId = 'forumSearch';
$topbarSearchPlaceholder = 'Buscar discusiones...';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <?php if (isset($_GET['success'])): ?><div class="success-msg" style="background:#DCFCE7; color:#166534; padding:15px; border-radius:10px; margin-bottom:20px; border:1px solid #4ADE80;"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div><?php
endif; ?>

                <div style="display:flex; justify-content:flex-end;">
                    <?php if (true): // Todos pueden preguntar ?>
                        <button onclick="document.getElementById('postModal').style.display='flex'" class="btn-primary" style="width:auto;"><i class="fas fa-plus"></i> Nueva Pregunta</button>
                    <?php
endif; ?>
                </div>
                <div class="forum-layout">
                    <div class="cat-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                            <h4 style="margin:0; color:var(--text-secondary); text-transform:uppercase; font-size:12px;">Categorías</h4>
                            <?php if ($isAdmin): ?>
                            <button onclick="document.getElementById('catModal').style.display='flex'" style="background:none; border:none; color:var(--accent-color); font-size:12px; cursor:pointer; font-weight:700;">+ Crear</button>
                            <?php
endif; ?>
                        </div>

                        <div class="cat-btn active" onclick="filterTopics('all', this)"><span><i class="fas fa-globe"></i> Todo</span></div>
                        <?php foreach ($categories as $cat): ?>
                            <div class="cat-btn" onclick="filterTopics('<?php echo $cat['id']; ?>', this)">
                                <span><i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($cat['nombre']); ?></span>
                                <?php if ($isAdmin): ?>
                                <a href="index.php?action=delete_forum_category&id=<?php echo $cat['id']; ?>" class="cat-del-btn" onclick="return confirm('¿Borrar categoría?')"><i class="fas fa-trash"></i></a>
                                <?php
    endif; ?>
                            </div>
                        <?php
endforeach; ?>
                    </div>

                    <div id="topicsFeed" class="topics-scroller">
                        <?php foreach ($topics as $topic):
    $avatar = $topic['es_anonimo'] ? '<?php echo BASE_URL; ?>assets/img/anon.png' : ($topic['foto_perfil'] ? '<?php echo BASE_URL; ?>assets/uploads/profiles/' . $topic['foto_perfil'] : '<?php echo BASE_URL; ?>assets/uploads/profiles/default.png');
    $authorName = $topic['es_anonimo'] ? 'Anónimo' : $topic['autor_nombre'] . ' ' . $topic['autor_apellido'];
    $searchData = strtolower($topic['titulo'] . ' ' . $topic['contenido']);
?>
                        <div class="topic-card" data-cat="<?php echo $topic['categoria_id']; ?>" data-search="<?php echo htmlspecialchars($searchData, ENT_QUOTES); ?>" onclick="openTopic(<?php echo htmlspecialchars(json_encode($topic), ENT_QUOTES, 'UTF-8'); ?>)">
                            <div class="topic-header">
                                <img src="<?php echo $avatar; ?>" class="topic-avatar">
                                <div class="topic-meta">
                                    <div class="topic-author"><?php echo htmlspecialchars($authorName); ?></div>
                                    <div class="topic-date"><?php echo date('d M, H:i', strtotime($topic['creado_en'])); ?></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span class="topic-cat-badge"><?php echo htmlspecialchars($topic['categoria_nombre']); ?></span>
                                    <?php if ($isAdmin): ?>
                                    <a class="btn-delete-topic" href="index.php?action=delete_topic&id=<?php echo $topic['id']; ?>" onclick="event.stopPropagation(); return confirm('¿Borrar tema?');" title="Borrar tema"><i class="fas fa-trash"></i></a>
                                    <?php
    endif; ?>
                                </div>
                            </div>
                            <h3 class="topic-title"><?php echo htmlspecialchars($topic['titulo']); ?></h3>
                            <div class="topic-preview"><?php echo strip_tags($topic['contenido']); ?></div>
                            <div class="topic-footer">
                                <div class="topic-stat <?php echo $topic['user_liked'] ? 'liked' : ''; ?>">
                                    <i class="<?php echo $topic['user_liked'] ? 'fas' : 'far'; ?> fa-heart"></i> <?php echo $topic['likes_count']; ?> Likes
                                </div>
                                <div class="topic-stat"><i class="far fa-comment-alt"></i> <?php echo $topic['comments_count']; ?> Comentarios</div>
                                <div class="topic-stat"><i class="far fa-eye"></i> <?php echo $topic['vistas']; ?></div>
                            </div>
                        </div>
                        <?php
endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="topicModal" class="modal">
        <div class="modal-content">
            <div class="mc-header">
                <h3 style="margin:0; font-size:16px;">Discusión</h3>
                <div style="display:flex; align-items:center; gap:15px;">
                    <?php if ($isAdmin): ?>
                    <a id="modalDeleteTopicBtn" href="#" style="background:none; border:none; color:#EF4444; cursor:pointer; text-decoration:none;" title="Eliminar tema" onclick="return confirm('¿Borrar tema?');"><i class="fas fa-trash fa-lg"></i></a>
                    <?php
endif; ?>
                    <button onclick="document.getElementById('topicModal').style.display='none'" style="background:none; border:none; color:var(--text-secondary); cursor:pointer;"><i class="fas fa-times fa-lg"></i></button>
                </div>
            </div>
            
            <div class="mc-scroll" id="topicDetailContent"></div>

            <div class="comment-box">
                <img src="<?php echo BASE_URL; ?>assets/uploads/profiles/<?php echo htmlspecialchars($foto_perfil); ?>" style="width:35px; height:35px; border-radius:50%;">
                <input type="hidden" id="currentTopicId">
                <textarea id="commentInput" class="comment-input" placeholder="Escribe un comentario..."></textarea>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <button class="btn-send" onclick="sendComment()"><i class="fas fa-paper-plane"></i></button>
                    <label style="font-size:10px; color:var(--text-secondary); cursor:pointer;">
                        <input type="checkbox" id="commentAnon"> Anónimo
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div id="createModal" class="modal">
        <div class="modal-content" style="max-width:700px; height:auto; max-height:90vh;">
            <div class="mc-header">
                <h3>Iniciar Nueva Discusión</h3>
                <button onclick="document.getElementById('createModal').style.display='none'" style="background:none; border:none;"><i class="fas fa-times"></i></button>
            </div>
            <div style="padding:20px; overflow-y:auto; flex:1;">
                <form id="createForm" action="index.php?action=create_topic" method="POST" enctype="multipart/form-data">
                    <input type="text" name="titulo" class="form-input" placeholder="Título de la discusión" required>
                    <select name="categoria_id" class="form-input" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                        <?php
endforeach; ?>
                    </select>
                    
                    <div style="margin-bottom:20px;">
                        <div class="ai-toolbar">
                            <div style="font-size:11px; font-weight:800; color:#7C3AED; display:flex; align-items:center; gap:5px;"><i class="fas fa-robot"></i> ASISTENTE IA:</div>
                            <button type="button" class="btn-ai" onclick="callAI('improve')"><i class="fas fa-spell-check"></i> Ortografía</button>
                            <button type="button" class="btn-ai" onclick="callAI('friendly')"><i class="fas fa-smile"></i> Tono Amigable</button>
                        </div>
                        <div id="quill-editor"></div>
                        <input type="hidden" name="contenido" id="hiddenContent">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="font-size:12px; font-weight:700; color:var(--text-secondary);">Imagen (Opcional)</label>
                        <input type="file" name="imagen" class="form-input">
                    </div>
                    <label style="display:flex; align-items:center; gap:10px; margin-bottom:20px; font-size:14px; color:var(--text-main);">
                        <input type="checkbox" name="is_anonymous"> Publicar de forma anónima
                    </label>
                    <button type="submit" class="btn-primary">Publicar</button>
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
                <form action="index.php?action=create_forum_category" method="POST">
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:700; font-size:12px;">Nombre</label>
                        <input type="text" name="nombre" class="form-input" required placeholder="Ej: Deportes">
                    </div>
                    <button type="submit" class="btn-primary">Guardar</button>
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
                    placeholder: 'Escribe aquí...',
                    modules: { toolbar: [['bold', 'italic'], [{'list':'ordered'},{'list':'bullet'}], ['link']] }
                });
            }
        }

        // Form Submit
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

        // Filtrado y Búsqueda
        function filterTopics(catId, btn) {
            document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.topic-card').forEach(card => {
                if(catId === 'all' || card.getAttribute('data-cat') == catId) card.style.display = 'block';
                else card.style.display = 'none';
            });
        }

        document.getElementById('forumSearch').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.topic-card').forEach(card => {
                const visible = card.getAttribute('data-search').includes(term);
                card.style.display = visible ? 'block' : 'none';
            });
        });

        function openTopic(topic) {
            document.getElementById('currentTopicId').value = topic.id;
            const modalDeleteBtn = document.getElementById('modalDeleteTopicBtn');
            if(modalDeleteBtn) {
                modalDeleteBtn.href = 'index.php?action=delete_topic&id=' + topic.id;
            }
            const container = document.getElementById('topicDetailContent');
            
            let avatar = topic.es_anonimo == 1 ? '<?php echo BASE_URL; ?>assets/img/anon.png' : (topic.foto_perfil ? '<?php echo BASE_URL; ?>assets/uploads/profiles/' + topic.foto_perfil : '<?php echo BASE_URL; ?>assets/uploads/profiles/default.png');
            let name = topic.es_anonimo == 1 ? 'Anónimo' : topic.autor_nombre + ' ' + topic.autor_apellido;
            
            let html = `
                <div style="margin-bottom:30px;">
                    <div style="display:flex; gap:15px; margin-bottom:15px;">
                        <img src="${avatar}" style="width:50px; height:50px; border-radius:50%;">
                        <div>
                            <h2 style="margin:0; font-size:20px; color:var(--text-main);">${topic.titulo}</h2>
                            <div style="font-size:13px; color:var(--text-secondary);">Por <strong>${name}</strong></div>
                        </div>
                    </div>
                    <div style="color:var(--text-main); line-height:1.6;">${DOMPurify.sanitize(topic.contenido)}</div>
                    ${topic.imagen ? `<img src="${topic.imagen}" style="width:100%; border-radius:10px; margin-top:15px;">` : ''}
                    <div style="margin-top:15px;">
                        <button class="topic-stat" style="background:none; border:none; cursor:pointer;" onclick="likeTopicAjax(${topic.id}, this)">
                            <i class="${topic.user_liked ? 'fas' : 'far'} fa-heart" style="${topic.user_liked ? 'color:#EF4444;' : ''}"></i> <span>${topic.likes_count}</span>
                        </button>
                    </div>
                </div>
                <h4 style="border-bottom:1px solid var(--border-subtle); padding-bottom:10px; margin-bottom:20px; color:var(--text-main);">Comentarios</h4>
                <div id="commentsList">Cargando...</div>
            `;
            container.innerHTML = html;
            document.getElementById('topicModal').style.display = 'flex';
            if(topic.comments) renderComments(topic.comments); else fetchComments(topic.id);
            fetch('index.php?action=ajax_forum_view', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'tema_id='+topic.id});
        }

        // Placeholder para fetch real si los comentarios no vienen en el objeto inicial (Opcional si optimizas el Modelo)
        function fetchComments(id) { document.getElementById('commentsList').innerHTML = ''; } 

        function sendComment() {
            const txt = document.getElementById('commentInput').value;
            const tid = document.getElementById('currentTopicId').value;
            const anon = document.getElementById('commentAnon').checked;
            if(!txt.trim()) return;
            
            fetch('index.php?action=ajax_forum_comment', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `tema_id=${tid}&content=${encodeURIComponent(txt)}&is_anonymous=${anon}`
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('commentInput').value = '';
                    const list = document.getElementById('commentsList');
                    if(list.innerText.includes('Cargando')) list.innerHTML = '';
                    let ava = anon ? '<?php echo BASE_URL; ?>assets/img/anon.png' : '<?php echo "<?php echo BASE_URL; ?>assets/uploads/profiles/" . $foto_perfil; ?>';
                    let nom = anon ? 'Anónimo' : '<?php echo $currentUser['nombre'] . " " . $currentUser['apellido']; ?>';
                    list.innerHTML += `<div class="comment-item"><img src="${ava}" style="width:35px; height:35px; border-radius:50%;"><div class="comment-bubble"><div class="comment-meta"><span>${nom}</span> <span>Ahora</span></div><div style="color:var(--text-main); font-size:14px;">${DOMPurify.sanitize(txt)}</div></div></div>`;
                }
            });
        }

        function likeTopicAjax(id, btn) {
            fetch('index.php?action=ajax_forum_like', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'tema_id='+id})
            .then(r => r.json()).then(data => {
                if(data.success) {
                    const icon = btn.querySelector('i'); const span = btn.querySelector('span');
                    if(data.action === 'liked') { icon.className = 'fas fa-heart'; icon.style.color = '#EF4444'; } 
                    else { icon.className = 'far fa-heart'; icon.style.color = ''; }
                    span.innerText = data.likes_count;
                }
            });
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