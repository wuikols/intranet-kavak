<?php
// Ya tenemos $tasksList y $usersList desde TaskController
if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}

global $globalQuickLinks;
$db = (new Database())->getConnection();
$currentUser = (new User($db))->getUserById($_SESSION['user_id']);

$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
$nombre_mostrar = htmlspecialchars($currentUser['apodo'] ?: explode(' ', $currentUser['nombre'])[0]);

// Agrupar tareas por estado
$tareasAgrupadas = [
    'por_hacer' => [],
    'en_curso' => [],
    'completado' => []
];

foreach ($tasksList as $task) {
    $estado = $task['estado'] ?? 'por_hacer';
    if (isset($tareasAgrupadas[$estado])) {
        $tareasAgrupadas[$estado][] = $task;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Mis Tareas - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
        
        .page-header { background: linear-gradient(135deg, #8B5CF6 0%, #4C1D95 100%); color: white; padding: 40px; border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(139, 92, 246, 0.5); margin-bottom: 30px; position: relative; overflow: hidden; }
        .page-header h1 { margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -1px; }
        .page-header p { margin: 10px 0 0 0; font-size: 15px; opacity: 0.9; }
        .ph-icon { font-size: 100px; position: absolute; right: 5%; top: -10px; opacity: 0.1; transform: rotate(15deg); }

        .tasks-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; align-items: start;}
        @media(max-width:900px) { .tasks-grid { grid-template-columns: 1fr; } }
        
        .task-column { background: var(--input-bg); border-radius: 24px; padding: 20px; border: 1px solid var(--border-subtle); min-height: 500px; display:flex; flex-direction:column; gap:15px; transition: 0.3s; }
        .task-column.drag-over { background: var(--hover-bg); border: 2px dashed var(--accent-color); box-shadow: inset 0 0 20px rgba(139, 92, 246, 0.1); }
        .col-header { display: flex; justify-content: space-between; align-items: center; font-weight: 800; font-size: 14px; text-transform: uppercase; margin-bottom: 10px; color: var(--text-main); }
        .col-badge { background: var(--card-bg); color: var(--text-secondary); padding: 4px 10px; border-radius: 12px; font-size: 12px; }
        
        .task-card { background: var(--card-bg); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-subtle); cursor: grab; transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1); position:relative; animation: slideUp 0.4s ease-out; }
        .task-card:active { cursor: grabbing; transform: scale(0.98); opacity: 0.8; }
        .task-card:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 12px 20px -10px rgba(0,0,0,0.15); border-color: var(--accent-color); }
        
        @keyframes slideUp { from {opacity: 0; transform: translateY(20px);} to {opacity: 1; transform: translateY(0);} }

        .task-title { font-weight: 700; font-size: 15px; color: var(--text-main); margin-bottom: 8px; }
        .task-desc {font-size:13px; color:var(--text-secondary); line-height:1.4; white-space: pre-wrap;}
        .task-meta { font-size: 12px; color: var(--text-secondary); display: flex; gap: 15px; align-items: center; margin-top:15px; border-top: 1px dashed var(--border-subtle); padding-top: 10px; justify-content: space-between;}
        
        .priority-alta { border-left: 4px solid #EF4444 !important; }
        .priority-media { border-left: 4px solid #F59E0B !important; }
        .priority-baja { border-left: 4px solid #10B981 !important; }

        .btn-add { width: 100%; border: 2px dashed var(--border-subtle); background: transparent; color: var(--text-secondary); padding: 15px; border-radius: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; margin-top: auto; }
        .btn-add:hover { border-color: var(--accent-color); color: var(--accent-color); background: var(--card-bg); transform: translateY(-2px); box-shadow: var(--shadow-sm); }
        
        .delete-task-btn { position:absolute; top: 15px; right: 15px; background:none; border:none; color: var(--text-secondary); opacity: 0; transition: 0.2s; cursor:pointer;}
        .task-card:hover .delete-task-btn { opacity: 1; }
        .delete-task-btn:hover { color: #EF4444; transform: scale(1.1); }

        /* MODAL STYLES (Glassmorphism) */
        .modal-overlay { position: fixed; top: 0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(8px); display: flex; justify-content: center; align-items: center; z-index: 1000; opacity: 0; visibility: hidden; transition: 0.3s; }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-content { background: var(--card-bg); width: 90%; max-width: 500px; padding: 40px; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); border: 1px solid var(--border-subtle); transform: translateY(30px) scale(0.95); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .modal-overlay.active .modal-content { transform: translateY(0) scale(1); }
        
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .modal-header h2 { margin: 0; font-size: 24px; font-weight: 800; color: var(--text-main); }
        .close-modal { background: none; border: none; font-size: 24px; color: var(--text-secondary); cursor: pointer; transition: 0.2s; }
        .close-modal:hover { color: #EF4444; transform: rotate(90deg); }

        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
        .form-control { width: 100%; padding: 14px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 12px; color: var(--text-main); font-family: 'Inter', sans-serif; font-size: 14px; transition: 0.2s; box-sizing: border-box;}
        .form-control:focus { outline: none; border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1); background: var(--card-bg);}
        
        .btn-primary { width: 100%; background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); color: white; border: none; padding: 16px; border-radius: 12px; font-size: 15px; font-weight: 700; cursor: pointer; transition: 0.3s; display:inline-block; text-align:center;}
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 20px -10px rgba(139, 92, 246, 0.6); }

    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'Gestor de Tareas';
$topbarBadge = 'Productividad';
include 'partials/topbar.php';
?>

            <section class="content-area">
                <div class="page-header">
                    <i class="fas fa-tasks ph-icon"></i>
                    <h1>Mis Tareas Asignadas</h1>
                    <p>Organiza tu día de forma ágil. Arrastra las tareas entre columnas para actualizar su progreso.</p>
                </div>

                <div class="tasks-grid">
                    <!-- Columna: Por Hacer -->
                    <div class="task-column" data-status="por_hacer" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="leaveDrag(event)">
                        <div class="col-header">
                            <div><i class="fas fa-circle" style="color:#F3F4F6; margin-right:5px; font-size:10px;"></i> Por Hacer</div>
                            <span class="col-badge" id="count-por_hacer"><?php echo count($tareasAgrupadas['por_hacer']); ?></span>
                        </div>
                        
                        <?php foreach ($tareasAgrupadas['por_hacer'] as $task): ?>
                        <div class="task-card priority-<?php echo $task['prioridad']; ?>" draggable="true" ondragstart="drag(event)" id="task-<?php echo $task['id']; ?>" data-id="<?php echo $task['id']; ?>" onclick="openReadModal(this, <?php echo htmlspecialchars(json_encode($task), ENT_QUOTES, 'UTF-8'); ?>)">
                            <a href="index.php?action=delete_task&id=<?php echo $task['id']; ?>" class="delete-task-btn" onclick="event.stopPropagation(); return confirm('¿Eliminar esta tarea?');" title="Eliminar"><i class="fas fa-trash"></i></a>
                            
                            <div class="task-title"><?php echo htmlspecialchars($task['titulo']); ?></div>
                            <div class="task-desc" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo nl2br(htmlspecialchars($task['descripcion'])); ?></div>
                            <div class="task-meta">
                                <span>
                                    <i class="far fa-clock"></i> 
                                    <?php echo $task['fecha_vencimiento'] ? date('d M, Y', strtotime($task['fecha_vencimiento'])) : 'Sin fecha'; ?>
                                </span>
                                <?php if (!empty($task['curso_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($task['curso_link']); ?>" target="_blank" onclick="event.stopPropagation();" style="font-size:11px; font-weight:700; background:rgba(37,99,235,0.1); color:#3B82F6; padding:3px 8px; border-radius:10px; text-decoration:none;"><i class="fas fa-external-link-alt"></i> Link</a>
                                <?php
    endif; ?>
                                <?php if ($task['asignado_nombre']): ?>
                                    <span style="font-weight: 600; color:var(--accent-color);"><i class="fas fa-user-circle"></i> <?php echo explode(' ', $task['asignado_nombre'])[0]; ?></span>
                                <?php
    endif; ?>
                            </div>
                        </div>
                        <?php
endforeach; ?>

                        <button class="btn-add trigger-modal"><i class="fas fa-plus"></i> Nueva Tarea</button>
                    </div>

                    <!-- Columna: En Curso -->
                    <div class="task-column" data-status="en_curso" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="leaveDrag(event)">
                        <div class="col-header">
                            <div><i class="fas fa-spinner" style="color:#3B82F6; margin-right:5px; font-size:10px;"></i> En Curso</div>
                            <span class="col-badge" id="count-en_curso"><?php echo count($tareasAgrupadas['en_curso']); ?></span>
                        </div>

                        <?php foreach ($tareasAgrupadas['en_curso'] as $task): ?>
                        <div class="task-card priority-<?php echo $task['prioridad']; ?>" draggable="true" ondragstart="drag(event)" id="task-<?php echo $task['id']; ?>" data-id="<?php echo $task['id']; ?>" onclick="openReadModal(this, <?php echo htmlspecialchars(json_encode($task), ENT_QUOTES, 'UTF-8'); ?>)">
                            <a href="index.php?action=delete_task&id=<?php echo $task['id']; ?>" class="delete-task-btn" onclick="event.stopPropagation(); return confirm('¿Eliminar esta tarea?');" title="Eliminar"><i class="fas fa-trash"></i></a>
                            
                            <div class="task-title"><?php echo htmlspecialchars($task['titulo']); ?></div>
                            <div class="task-desc" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo nl2br(htmlspecialchars($task['descripcion'])); ?></div>
                            <div class="task-meta">
                                <span><i class="fas fa-spinner fa-spin"></i> Activo</span>
                                <?php if (!empty($task['curso_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($task['curso_link']); ?>" target="_blank" onclick="event.stopPropagation();" style="font-size:11px; font-weight:700; background:rgba(37,99,235,0.1); color:#3B82F6; padding:3px 8px; border-radius:10px; text-decoration:none;"><i class="fas fa-external-link-alt"></i> Link</a>
                                <?php
    endif; ?>
                                <?php if ($task['asignado_nombre']): ?>
                                    <span style="font-weight: 600; color:var(--accent-color);"><i class="fas fa-user-circle"></i> <?php echo explode(' ', $task['asignado_nombre'])[0]; ?></span>
                                <?php
    endif; ?>
                            </div>
                        </div>
                        <?php
endforeach; ?>
                    </div>

                    <!-- Columna: Completado -->
                    <div class="task-column" data-status="completado" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="leaveDrag(event)" style="opacity: 0.9;">
                        <div class="col-header">
                            <div><i class="fas fa-check-circle" style="color:#10B981; margin-right:5px; font-size:10px;"></i> Completado</div>
                            <span class="col-badge" id="count-completado"><?php echo count($tareasAgrupadas['completado']); ?></span>
                        </div>

                        <?php foreach ($tareasAgrupadas['completado'] as $task): ?>
                        <div class="task-card" draggable="true" ondragstart="drag(event)" id="task-<?php echo $task['id']; ?>" data-id="<?php echo $task['id']; ?>" style="border-left: 4px solid #10B981;" onclick="openReadModal(this, <?php echo htmlspecialchars(json_encode($task), ENT_QUOTES, 'UTF-8'); ?>)">
                            <a href="index.php?action=delete_task&id=<?php echo $task['id']; ?>" class="delete-task-btn" onclick="event.stopPropagation(); return confirm('¿Eliminar esta tarea?');" title="Eliminar"><i class="fas fa-trash"></i></a>
                            
                            <div class="task-title" style="text-decoration: line-through; opacity: 0.7;"><?php echo htmlspecialchars($task['titulo']); ?></div>
                            <div class="task-meta">
                                <span style="color:#10B981;"><i class="fas fa-check"></i> Terminado</span>
                                <?php if (!empty($task['curso_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($task['curso_link']); ?>" target="_blank" onclick="event.stopPropagation();" style="font-size:11px; font-weight:700; background:rgba(37,99,235,0.1); color:#3B82F6; padding:3px 8px; border-radius:10px; text-decoration:none;"><i class="fas fa-external-link-alt"></i> Link</a>
                                <?php
    endif; ?>
                            </div>
                        </div>
                        <?php
endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MODAL NUEVA TAREA -->
    <div class="modal-overlay" id="taskModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nueva Tarea</h2>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <form action="index.php?action=create_task" method="POST">
                <div class="form-group">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Ej: Revisar reporte quincenal">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Detalles de lo que se debe hacer..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Link de Curso / Material Módulo (Opcional)</label>
                    <input type="url" name="curso_link" class="form-control" placeholder="https:// ...">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-control" required>
                            <option value="baja">Baja - Verde</option>
                            <option value="media" selected>Media - Naranja</option>
                            <option value="alta">Alta - Roja</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Vencimiento</label>
                        <input type="date" name="fecha_vencimiento" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Asignar A</label>
                    <select name="asignacion" class="form-control" required style="max-height: 200px;">
                        <optgroup label="Asignación Especial">
                            <option value="user_<?php echo $_SESSION['user_id']; ?>">A mi mismo</option>
                            <option value="all">Asignar a TODA la empresa</option>
                        </optgroup>
                        
                        <optgroup label="Por Hub / Sucursal">
                            <?php foreach ($sucursales as $s): ?>
                                <option value="hub_<?php echo $s['id']; ?>">📍 A todos en: <?php echo htmlspecialchars($s['nombre']); ?></option>
                            <?php
endforeach; ?>
                        </optgroup>
                        
                        <optgroup label="Por Área / Cargo">
                            <?php foreach ($cargos as $c): ?>
                                <option value="area_<?php echo $c['id']; ?>">💼 A todos en: <?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php
endforeach; ?>
                        </optgroup>

                        <optgroup label="Usuarios Específicos">
                            <?php foreach ($usersList as $user): ?>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <option value="user_<?php echo $user['id']; ?>">👤 <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?> (<?php echo htmlspecialchars($user['sucursal_nombre'] ?? 'Sin hub'); ?>)</option>
                                <?php
    endif; ?>
                            <?php
endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="margin-top:10px;">
                    <i class="fas fa-plus-circle"></i> Añadir Tarea
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL LECTURA / DETALLE TAREA -->
    <div class="modal-overlay" id="readTaskModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="rTaskTitle"></h2>
                <button class="close-modal" id="closeReadModal">&times;</button>
            </div>
            
            <div style="margin-bottom:20px;">
                <span id="rTaskMeta" style="font-size:12px; font-weight:700; color:var(--text-secondary); text-transform:uppercase; display:inline-block; margin-bottom:15px; background:var(--input-bg); padding:4px 10px; border-radius:6px;"></span>
                <div id="rTaskDesc" style="font-size:14px; line-height:1.6; color:var(--text-secondary); white-space:pre-wrap; background:var(--card-bg); border:1px solid var(--border-subtle); padding:15px; border-radius:12px;"></div>
            </div>

            <div id="rTaskActions" style="display:flex; gap:10px; flex-direction:column; margin-top:20px;">
                <a id="rTaskLinkBtn" href="#" target="_blank" onclick="event.stopPropagation();" class="btn-primary" style="background:rgba(37,99,235,0.1); color:#3B82F6; display:none;"><i class="fas fa-external-link-alt"></i> Material Asociado / Curso</a>
                
                <!-- BOTON FINALIZAR - SOLO SE MUESTRA EN CURSO -->
                <button id="rTaskFinishBtn" class="btn-primary" style="background:#10B981; display:none;"><i class="fas fa-check-circle"></i> Terminar Tarea</button>
            </div>
        </div>
    </div>
    
    <script>
        // Modal Logic
        const modal = document.getElementById('taskModal');
        const trigger = document.querySelector('.trigger-modal');
        const closeBtn = document.getElementById('closeModal');

        trigger.addEventListener('click', () => modal.classList.add('active'));
        closeBtn.addEventListener('click', () => modal.classList.remove('active'));
        window.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('active') });

        // Dark pattern setup
        const themeToggle = document.getElementById('themeToggle'); const body = document.body;
        if(localStorage.getItem('theme') === 'dark') body.classList.add('dark-mode');
        themeToggle.addEventListener('click', () => { body.classList.toggle('dark-mode'); localStorage.setItem('theme', body.classList.contains('dark-mode') ? 'dark' : 'light'); });

        // Drag and Drop Logic
        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
            ev.dataTransfer.effectAllowed = "move";
            setTimeout(() => { ev.target.style.opacity = '0.5'; }, 0);
        }

        function allowDrop(ev) {
            ev.preventDefault();
            let col = ev.target.closest('.task-column');
            if(col && !col.classList.contains('drag-over')) {
                document.querySelectorAll('.task-column').forEach(c => c.classList.remove('drag-over'));
                col.classList.add('drag-over');
            }
        }

        function leaveDrag(ev) {
            let col = ev.target.closest('.task-column');
            if(col) col.classList.remove('drag-over');
        }

        function drop(ev) {
            ev.preventDefault();
            let col = ev.target.closest('.task-column');
            if(col) {
                col.classList.remove('drag-over');
                var data = ev.dataTransfer.getData("text");
                const card = document.getElementById(data);
                if(!card) return;
                
                let oldCol = card.closest('.task-column');
                
                card.style.opacity = '1';
                const addBtn = col.querySelector('.btn-add');
                if(addBtn) {
                    col.insertBefore(card, addBtn);
                } else {
                    col.appendChild(card);
                }

                // Update counts
                if(oldCol && oldCol !== col) {
                    const oldCountEl = oldCol.querySelector('.col-badge');
                    const newCountEl = col.querySelector('.col-badge');
                    if(oldCountEl) oldCountEl.innerText = parseInt(oldCountEl.innerText) - 1;
                    if(newCountEl) newCountEl.innerText = parseInt(newCountEl.innerText) + 1;
                }

                const newStatus = col.getAttribute('data-status');
                const taskId = card.getAttribute('data-id');

                fetch('index.php?action=update_task_status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: taskId, estado: newStatus })
                })
                .then(res => res.json())
                .then(data => {
                    if(!data.success) {
                        alert("Hubo un error al actualizar la tarea.");
                    } else {
                        // Optionally update visual styles for 'completado'
                        if(newStatus === 'completado') {
                            card.querySelector('.task-title').style.textDecoration = 'line-through';
                            card.querySelector('.task-title').style.opacity = '0.7';
                            card.style.borderLeft = '4px solid #10B981';
                        } else {
                            card.querySelector('.task-title').style.textDecoration = 'none';
                            card.querySelector('.task-title').style.opacity = '1';
                            // Restore actual priority border based on class
                            if(card.classList.contains('priority-alta')) card.style.borderLeft = '4px solid #EF4444';
                            else if(card.classList.contains('priority-media')) card.style.borderLeft = '4px solid #F59E0B';
                            else card.style.borderLeft = '4px solid #10B981';
                        }
                    }
                }).catch(err => console.error(err));
            }
        }
        
        // Restore opacity on drag end even if outside
        document.addEventListener('dragend', (ev) => {
            if(ev.target && ev.target.classList && ev.target.classList.contains('task-card')){
                ev.target.style.opacity = '1';
            }
            document.querySelectorAll('.task-column').forEach(c => c.classList.remove('drag-over'));
        });

        // Automatización de Lectura y Finalizar Tarea
        const readModal = document.getElementById('readTaskModal');
        const closeReadBtn = document.getElementById('closeReadModal');
        let currentReadTaskId = null;

        closeReadBtn.addEventListener('click', () => readModal.classList.remove('active'));
        window.addEventListener('click', (e) => { if (e.target === readModal) readModal.classList.remove('active') });

        function openReadModal(cardElement, taskObj) {
            currentReadTaskId = taskObj.id;
            document.getElementById('rTaskTitle').innerText = taskObj.titulo;
            document.getElementById('rTaskDesc').innerText = taskObj.descripcion || 'Sin descripción adicional.';
            
            let statusText = taskObj.estado.replace('_', ' ').toUpperCase();
            document.getElementById('rTaskMeta').innerText = "ESTADO: " + statusText;

            const linkBtn = document.getElementById('rTaskLinkBtn');
            if(taskObj.curso_link) {
                linkBtn.href = taskObj.curso_link;
                linkBtn.style.display = 'block';
            } else {
                linkBtn.style.display = 'none';
            }

            const finishBtn = document.getElementById('rTaskFinishBtn');

            // Lógica de transición automática
            let currentStatus = cardElement.closest('.task-column').getAttribute('data-status');
            
            if (currentStatus === 'por_hacer') {
                // Mover a 'en_curso' automáticamente
                let enCursoCol = document.querySelector('.task-column[data-status="en_curso"]');
                if(enCursoCol) {
                    const addBtn = enCursoCol.querySelector('.btn-add');
                    if(addBtn) {
                        enCursoCol.insertBefore(cardElement, addBtn);
                    } else {
                        enCursoCol.appendChild(cardElement);
                    }
                    
                    // Actualizar contadores
                    let oldC = document.getElementById('count-por_hacer');
                    let newC = document.getElementById('count-en_curso');
                    if(oldC) oldC.innerText = parseInt(oldC.innerText) - 1;
                    if(newC) newC.innerText = parseInt(newC.innerText) + 1;
                    
                    fetch('index.php?action=update_task_status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: taskObj.id, estado: 'en_curso' })
                    });
                    
                    document.getElementById('rTaskMeta').innerText = "ESTADO: EN CURSO";
                    currentStatus = 'en_curso';
                }
            }

            if (currentStatus === 'en_curso') {
                finishBtn.style.display = 'block';
                finishBtn.onclick = function() {
                    markTaskCompleted(cardElement, taskObj.id);
                };
            } else {
                finishBtn.style.display = 'none';
            }

            readModal.classList.add('active');
        }

        function markTaskCompleted(cardElement, taskId) {
            fetch('index.php?action=update_task_status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: taskId, estado: 'completado' })
            }).then(() => {
                // Mover visualmente
                let compCol = document.querySelector('.task-column[data-status="completado"]');
                
                if(compCol) {
                    compCol.appendChild(cardElement);
                    
                    let oldC = document.getElementById('count-en_curso');
                    let newC = document.getElementById('count-completado');
                    if(oldC) oldC.innerText = parseInt(oldC.innerText) - 1;
                    if(newC) newC.innerText = parseInt(newC.innerText) + 1;

                    cardElement.querySelector('.task-title').style.textDecoration = 'line-through';
                    cardElement.querySelector('.task-title').style.opacity = '0.7';
                    cardElement.style.borderLeft = '4px solid #10B981';
                    
                    // Cambiar el icono "Activo" a "Terminado"
                    let metaSpan = cardElement.querySelector('.task-meta > span');
                    if(metaSpan) {
                        metaSpan.innerHTML = '<i class="fas fa-check"></i> Terminado';
                        metaSpan.style.color = '#10B981';
                    }
                    
                    // Ocultar botón finalizer on card level logic later
                    finishBtn.style.display = 'none';
                }
                readModal.classList.remove('active');
            });
        }
    </script>
</body>
</html>
