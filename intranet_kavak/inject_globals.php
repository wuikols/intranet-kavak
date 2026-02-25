<?php
$dir = __DIR__ . '/views';
$files = glob($dir . '/*.php');

$injection_user_info = '                    <div class="user-info-top">
                        <div class="global-actions" style="display:flex; gap:15px; margin-right:15px; align-items:center;">
                            <button onclick="openUniversalSearch()" title="Buscar en Kavak (CTRL+K)" style="background:var(--input-bg); border:1px solid var(--border-subtle); border-radius:8px; padding:6px 12px; color:var(--text-secondary); cursor:pointer; font-weight:600; font-size:12px; display:flex; gap:8px; align-items:center; transition:0.2s;">
                                <i class="fas fa-search"></i> <span>CTRL+K</span>
                            </button>
                            <div style="position:relative;">
                                <button onclick="toggleNotifications()" style="background:none; border:none; font-size:18px; color:var(--text-secondary); cursor:pointer; position:relative; transition:0.2s;">
                                    <i class="fas fa-bell"></i>
                                    <span id="notif-badge" style="position:absolute; top:-5px; right:-5px; background:#EF4444; color:white; font-size:10px; font-weight:800; padding:2px 5px; border-radius:10px; display:none;">0</span>
                                </button>
                                <div id="notif-dropdown" style="display:none; position:absolute; right:0; top:35px; width:300px; background:var(--card-bg); border:1px solid var(--border-subtle); border-radius:12px; box-shadow:var(--shadow-lg); z-index:9000; overflow:hidden;">
                                    <div style="padding:15px; font-weight:800; border-bottom:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center; color:var(--text-main);">
                                        Notificaciones 
                                        <button onclick="markAllRead(event)" style="font-size:11px; color:var(--accent-color); background:none; border:none; cursor:pointer; font-weight:700;">Marcar leídas</button>
                                    </div>
                                    <div id="notif-list" style="max-height:300px; overflow-y:auto; padding:10px; font-size:13px; color:var(--text-secondary);">Cargando...</div>
                                </div>
                            </div>
                        </div>';

$injection_body = '
    <!-- UNIVERSAL SEARCH MODAL -->
    <div id="universalSearchModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.8); z-index:9999; align-items:flex-start; justify-content:center; padding-top:10vh;">
        <div style="background:var(--card-bg); width:90%; max-width:650px; border-radius:16px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); overflow:hidden; border:1px solid var(--border-subtle);">
            <div style="padding:20px; border-bottom:1px solid var(--border-subtle); display:flex; align-items:center; gap:15px; background:var(--input-bg);">
                <i class="fas fa-search" style="color:var(--accent-color); font-size:20px;"></i>
                <input type="text" id="universalSearchInput" placeholder="Busca empleados, foros, wiki o noticias..." style="flex:1; border:none; background:transparent; font-size:18px; color:var(--text-main); outline:none;">
                <button onclick="document.getElementById(\'universalSearchModal\').style.display=\'none\'" style="background:var(--card-bg); border:1px solid var(--border-subtle); color:var(--text-secondary); font-size:11px; font-weight:800; padding:6px 10px; border-radius:6px; cursor:pointer;">ESC</button>
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
                if(data.results.length === 0) res.innerHTML = "<div style=\'padding:20px;text-align:center;color:var(--text-secondary);\'>No se encontraron resultados.</div>";
                data.results.forEach(it => {
                    let icon = "fa-file"; let url = "#"; let color = "var(--text-secondary)";
                    if(it.type=="wiki"){icon="fa-book"; url="index.php?action=wiki"; color="#3B82F6";}
                    if(it.type=="foro"){icon="fa-comments"; url="index.php?action=forum"; color="#10B981";}
                    if(it.type=="user"){icon="fa-user"; url="index.php?action=directory"; color="#8B5CF6";}
                    if(it.type=="news"){icon="fa-bullhorn"; url="index.php?action=dashboard"; color="#F59E0B";}
                    
                    res.innerHTML += `
                        <a href="${url}" style="display:flex; align-items:center; gap:15px; padding:15px; text-decoration:none; border-radius:10px; transition:0.2s; margin-bottom:5px;" onmouseover="this.style.background=\'var(--input-bg)\'" onmouseout="this.style.background=\'transparent\'">
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
                
                if(data.items.length === 0) list.innerHTML = "<div style=\'padding:10px; text-align:center;\'>No tienes notificaciones nuevas.</div>";
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
</body>';

foreach ($files as $file) {
    if (in_array(basename($file), ['login.php', 'register.php', 'forgot_password.php', 'reset_password.php']))
        continue;

    $content = file_get_contents($file);
    $modified = false;

    // Inject Search and Notif button
    if (strpos($content, '<div class="user-info-top">') !== false && strpos($content, 'global-actions') === false) {
        $content = str_replace('<div class="user-info-top">', $injection_user_info, $content);
        $modified = true;
    }

    // Inject Scripts modal
    if (strpos($content, '</body>') !== false && strpos($content, 'UNIVERSAL SEARCH MODAL') === false) {
        $content = str_replace('</body>', $injection_body, $content);
        $modified = true;
    }

    if ($modified) {
        file_put_contents($file, $content);
        echo "Injected: " . basename($file) . "\n";
    }
}
echo "Injection done.";
?>
