<?php
// Default values for the topbar
$topbarTitle = $topbarTitle ?? 'Gestor de Tareas';
$topbarBadge = $topbarBadge ?? '';

$topbarBadgeClass = $topbarBadgeClass ?? 'badge';

$topbarBadgeStyle = $topbarBadgeStyle ?? 'background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2);';

$localQuickLinksList = $quickLinksList ?? $GLOBALS['quickLinksList'] ?? null;
$localIsSuperAdmin = $isSuperAdmin ?? $GLOBALS['isSuperAdmin'] ?? false;
$localFotoPerfil = $foto_perfil ?? $GLOBALS['foto_perfil'] ?? 'default.png';
$localRolNombre = $rol_nombre ?? $GLOBALS['rol_nombre'] ?? 'USUARIO';

$topbarSearchId = $topbarSearchId ?? 'globalSearchInput';
$topbarSearchPlaceholder = $topbarSearchPlaceholder ?? 'Buscar en Kavak...';
$topbarSearchConfig = $topbarSearchId === 'globalSearchInput'
    ? 'autocomplete="off" onmouseenter="this.focus()" oninput="handleGlobalSearch(this.value)"'
    : 'autocomplete="off" onmouseenter="this.focus()"';
?>
<header class="topbar">
    <div class="topbar-extended">
        <!-- Left + Center Group -->
        <div style="display:flex; align-items:center; flex:1; gap:20px; min-width:0;">
            <!-- Hamburger and Title -->
            <div style="display:flex; align-items:center;">
                <button class="mobile-menu-btn" id="openSidebar" style="background:none; border:none; color:var(--text-main); font-size:24px; cursor:pointer;"><i class="fas fa-bars"></i></button>
                <?php if (!empty($topbarTitle)): ?>
                    <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: var(--text-main); white-space: nowrap;"><?php echo htmlspecialchars($topbarTitle); ?></h1>
                <?php
endif; ?>
            </div>

            <!-- Search Bar moved directly after Title -->
            <div class="topbar-search" style="margin: 0; flex: 1; max-width: 400px; position:relative;" onmouseenter="document.getElementById('<?php echo $topbarSearchId; ?>').focus();">
                <i class="fas fa-search" style="color:var(--text-secondary); cursor:text;" onclick="document.getElementById('<?php echo $topbarSearchId; ?>').focus();"></i>
                <input type="text" id="<?php echo htmlspecialchars($topbarSearchId); ?>" placeholder="<?php echo htmlspecialchars($topbarSearchPlaceholder); ?>" <?php echo $topbarSearchConfig; ?>>
                
                <?php if ($topbarSearchId === 'globalSearchInput'): ?>
                <!-- Resultados Inline Globales -->
                <div id="inlineSearchResults" style="display:none; position:absolute; top:calc(100% + 5px); left:0; width:100%; background:var(--card-bg); border:1px solid var(--border-subtle); border-radius:12px; box-shadow:var(--shadow-lg); z-index:10000; max-height:400px; overflow-y:auto;">
                    <div id="inlineSearchContent" style="padding:10px;">
                        <!-- Los resultados se inyectarán aquí -->
                    </div>
                </div>
                <?php
endif; ?>
            </div>

            <!-- Quick Links shifted exactly after Search Bar -->
            <?php if (!empty($localQuickLinksList)): ?>
                <div style="width: 2px; height: 24px; background: var(--border-subtle); display:block;"></div>
                <div class="quick-links-container" style="display:flex; align-items:center; gap:8px;">
                    <?php foreach ($localQuickLinksList as $ql): ?>
                        <a href="<?php echo htmlspecialchars($ql['url']); ?>" target="_blank" class="ql-btn" title="<?php echo htmlspecialchars($ql['nombre']); ?>">
                            <i class="<?php echo htmlspecialchars($ql['icono']); ?>"></i>
                        </a>
                    <?php
    endforeach; ?>
                    <?php if (isset($localIsSuperAdmin) && $localIsSuperAdmin && $topbarTitle === 'Inicio'): ?>
                        <a href="#" class="ql-btn ql-admin-add" title="Añadir Enlace" onclick="if(document.getElementById('modalAddQuickLink')) document.getElementById('modalAddQuickLink').style.display='flex'"><i class="fas fa-plus"></i></a>
                    <?php
    endif; ?>
                </div>
            <?php
endif; ?>
        </div>

        <!-- Right Side -->
        <div class="user-info-top">
            <div class="global-actions" style="display:flex; gap:15px; margin-right:15px; align-items:center;">
                <!-- Notifications -->
                <div style="position:relative;">
                    <button onclick="if(typeof toggleNotifications === 'function') toggleNotifications();" style="background:none; border:none; font-size:18px; color:var(--text-secondary); cursor:pointer; position:relative; transition:0.2s;">
                        <i class="fas fa-bell"></i>
                        <span id="notif-badge" style="position:absolute; top:-5px; right:-5px; background:#EF4444; color:white; font-size:10px; font-weight:800; padding:2px 5px; border-radius:10px; display:none;">0</span>
                    </button>
                    <!-- Standard Dropdown for Notifications -->
                    <div id="notif-dropdown" style="display:none; position:absolute; right:0; top:35px; width:300px; background:var(--card-bg); border:1px solid var(--border-subtle); border-radius:12px; box-shadow:var(--shadow-lg); z-index:9000; overflow:hidden;">
                        <div style="padding:15px; font-weight:800; border-bottom:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center; color:var(--text-main);">
                            Notificaciones 
                            <button onclick="if(typeof markAllRead === 'function') markAllRead(event);" style="font-size:11px; color:var(--accent-color); background:none; border:none; cursor:pointer; font-weight:700;">Marcar leídas</button>
                        </div>
                        <div id="notif-list" style="max-height:300px; overflow-y:auto; padding:10px; font-size:13px; color:var(--text-secondary);">Cargando...</div>
                    </div>
                </div>
            </div>

            <!-- Custom User-Passed Module Badge -->
            <?php if (!empty($topbarBadge)): ?>
                <span class="<?php echo htmlspecialchars($topbarBadgeClass); ?>" style="<?php echo $topbarBadgeStyle; ?> font-size: 11px; padding: 6px 12px; border-radius: 8px; font-weight: 800; text-transform: uppercase;"><?php echo htmlspecialchars($topbarBadge); ?></span>
            <?php
endif; ?>

            <!-- Role Badge -->
            <span class="badge badge-admin" style="background: rgba(245, 158, 11, 0.15); color: #D97706; padding: 6px 12px; border-radius: 20px; font-weight: 800; font-size: 11px; text-transform: uppercase;">
                <?php echo htmlspecialchars($localRolNombre); ?>
            </span>

            <!-- User Avatar & Actions -->
            <a href="index.php?action=profile"><img src="/intranet_kavak/assets/uploads/profiles/<?php echo htmlspecialchars($localFotoPerfil); ?>" class="avatar-small" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 2px solid var(--card-bg);"></a>
            <a href="index.php?action=logout" style="color: #EF4444; font-size: 18px; margin-left:10px;" title="Cerrar Sesión"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</header>

<?php if (!defined('UNIVERSAL_SEARCH_INLINE_INCLUDED') && $topbarSearchId === 'globalSearchInput'):
    define('UNIVERSAL_SEARCH_INLINE_INCLUDED', true); ?>
<script>
    function handleGlobalSearch(val) {
        val = val.trim().toLowerCase();
        const resultsContainer = document.getElementById("inlineSearchResults");
        const content = document.getElementById("inlineSearchContent");
        
        if (!val) {
            resultsContainer.style.display = "none";
            return;
        }
        
        resultsContainer.style.display = "block";
        content.innerHTML = `<div style="padding:15px; border-bottom:1px solid var(--border-subtle); display:flex; align-items:center; gap:10px; cursor:pointer;" class="search-item">
            <div style="width:30px; height:30px; background:rgba(37,99,235,0.1); color:#2563EB; border-radius:8px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-search"></i></div>
            <div>
                <div style="font-weight:700; font-size:14px; color:var(--text-main);">Resultados en toda la Intranet para "${val}"</div>
                <div style="font-size:11px; color:var(--text-secondary);">Búsqueda Global</div>
            </div>
        </div>
        <div style="padding:10px 15px; font-size:11px; color:var(--text-secondary); text-align:center;">
            Presiona Enter para ver todos los resultados
        </div>`;
    }

    // Ocultar dropdown al hacer click fuera
    document.addEventListener("click", function(e) {
        let container = document.querySelector(".topbar-search");
        let results = document.getElementById("inlineSearchResults");
        if (results && container && !container.contains(e.target)) {
            results.style.display = "none";
        }
    });
</script>
<?php
endif; ?>
