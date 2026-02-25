<?php
if ($_SESSION['user_rol'] !== 'admin') { die("Acceso denegado."); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado - Admin</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <main class="main-content" style="width: 100%;">
            <header class="topbar">
                <h2>Administración / Editar Empleado</h2>
                <a href="index.php?action=admin_users" class="btn-login" style="width: auto; padding: 8px 15px; background: #6b7280;">Volver</a>
            </header>

            <section class="content-area">
                <div class="profile-box card">
                    <form action="index.php?action=update_user_admin" method="POST">
                        <input type="hidden" name="id" value="<?php echo $userToEdit['id'] ?? ''; ?>">
                        
                        <h3 style="margin-bottom: 20px;">Datos de Contratación</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Nombre Real</label>
                                <input type="text" name="nombre" value="<?php echo htmlspecialchars($userToEdit['nombre'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Apellido</label>
                                <input type="text" name="apellido" value="<?php echo htmlspecialchars($userToEdit['apellido'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Email Corporativo (Login)</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($userToEdit['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($userToEdit['fecha_nacimiento'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Fecha de Ingreso</label>
                                <input type="date" name="fecha_ingreso" value="<?php echo htmlspecialchars($userToEdit['fecha_ingreso'] ?? ''); ?>">
                            </div>
                        </div>

                        <h3 style="margin-bottom: 20px; margin-top: 20px;">Rol y Permisos</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Cargo Organizacional</label>
                                <input type="text" name="cargo" value="<?php echo htmlspecialchars($userToEdit['cargo'] ?? ''); ?>" placeholder="Ej: Especialista Financiero">
                            </div>
                            <div class="form-group">
                                <label>Nivel de Acceso (Sistema)</label>
                                <select name="rol">
                                    <option value="empleado" <?php if(($userToEdit['rol'] ?? '') == 'empleado') echo 'selected'; ?>>Empleado Básico</option>
                                    <option value="gerente" <?php if(($userToEdit['rol'] ?? '') == 'gerente') echo 'selected'; ?>>Gerente / Jefe</option>
                                    <option value="admin" <?php if(($userToEdit['rol'] ?? '') == 'admin') echo 'selected'; ?>>Administrador de IT</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-login" style="margin-top: 15px;">Guardar Cambios Oficiales</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

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