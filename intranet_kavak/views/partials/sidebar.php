<?php
$currentAction = $_GET['action'] ?? 'dashboard';

$moduleMiEspacio = ['tareas', 'solicitudes'];
$moduleComercial = ['cotizador', 'dyp_upgrade', 'nota_pase', 'formulario_pravia'];
$moduleColaboracion = ['directory', 'wiki', 'forum'];
$moduleAdmin = ['admin_news', 'admin_tips', 'admin_users', 'admin_company'];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header"><img src="/intranet_kavak/assets/img/LogoLetraBlanca.png" style="width: 130px !important;"></div>
    <nav class="sidebar-menu">
        
        <ul style="padding: 0; margin-bottom: 5px; list-style: none;">
            <li><a href="index.php?action=dashboard" class="<?php echo $currentAction === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Inicio</a></li>
        </ul>

        <div class="menu-module-toggle <?php echo in_array($currentAction, $moduleMiEspacio) ? 'open' : ''; ?>">
            <span>Mi Espacio</span> <i class="fas fa-chevron-down"></i>
        </div>
        <ul class="submenu-list <?php echo in_array($currentAction, $moduleMiEspacio) ? 'open' : ''; ?>">
            <li><a href="index.php?action=tareas" class="<?php echo $currentAction === 'tareas' ? 'active' : ''; ?>"><i class="fas fa-tasks"></i> Mis Tareas</a></li>
            <li><a href="index.php?action=solicitudes" class="<?php echo $currentAction === 'solicitudes' ? 'active' : ''; ?>"><i class="fas fa-ticket-alt"></i> Solicitudes</a></li>
        </ul>

        <div class="menu-module-toggle <?php echo in_array($currentAction, $moduleComercial) ? 'open' : ''; ?>">
            <span>Área Comercial</span> <i class="fas fa-chevron-down"></i>
        </div>
        <ul class="submenu-list <?php echo in_array($currentAction, $moduleComercial) ? 'open' : ''; ?>">
            <li><a href="index.php?action=cotizador" class="<?php echo $currentAction === 'cotizador' ? 'active' : ''; ?>"><i class="fas fa-calculator"></i> Cotizador Kavak</a></li>
            <li><a href="index.php?action=dyp_upgrade" class="<?php echo $currentAction === 'dyp_upgrade' ? 'active' : ''; ?>"><i class="fas fa-search-dollar"></i> DyP Upgrade</a></li>
            <li><a href="index.php?action=nota_pase" class="<?php echo $currentAction === 'nota_pase' ? 'active' : ''; ?>"><i class="fas fa-file-invoice-dollar"></i> Nota de Pase</a></li>
            <li><a href="index.php?action=formulario_pravia" class="<?php echo $currentAction === 'formulario_pravia' ? 'active' : ''; ?>"><i class="fas fa-car"></i> Formulario Pravia</a></li>
        </ul>

        <div class="menu-module-toggle <?php echo in_array($currentAction, $moduleColaboracion) ? 'open' : ''; ?>">
            <span>Colaboración</span> <i class="fas fa-chevron-down"></i>
        </div>
        <ul class="submenu-list <?php echo in_array($currentAction, $moduleColaboracion) ? 'open' : ''; ?>">
            <li><a href="index.php?action=directory" class="<?php echo $currentAction === 'directory' ? 'active' : ''; ?>"><i class="fas fa-address-book"></i> Directorio</a></li>
            <li><a href="index.php?action=wiki" class="<?php echo $currentAction === 'wiki' ? 'active' : ''; ?>"><i class="fas fa-book"></i> Wiki y Procesos</a></li>
            <li><a href="index.php?action=forum" class="<?php echo $currentAction === 'forum' ? 'active' : ''; ?>"><i class="fas fa-comments"></i> Foro</a></li>
        </ul>

        <?php if (!empty($_SESSION['p_noticias']) || !empty($_SESSION['p_usuarios']) || !empty($_SESSION['p_empresa'])): ?>
        <div class="menu-module-toggle <?php echo in_array($currentAction, $moduleAdmin) ? 'open' : ''; ?>">
            <span>Administración</span> <i class="fas fa-chevron-down"></i>
        </div>
        <ul class="submenu-list <?php echo in_array($currentAction, $moduleAdmin) ? 'open' : ''; ?>">
            <?php if (!empty($_SESSION['p_noticias'])): ?><li><a href="index.php?action=admin_news" class="<?php echo $currentAction === 'admin_news' ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Comunicaciones</a></li><?php
    endif; ?>
            <?php if (!empty($_SESSION['p_noticias'])): ?><li><a href="index.php?action=admin_tips" class="<?php echo $currentAction === 'admin_tips' ? 'active' : ''; ?>"><i class="fas fa-lightbulb"></i> Tips (Kavak)</a></li><?php
    endif; ?>
            <?php if (!empty($_SESSION['p_usuarios'])): ?><li><a href="index.php?action=admin_users" class="<?php echo $currentAction === 'admin_users' ? 'active' : ''; ?>"><i class="fas fa-users-cog"></i> Usuarios</a></li><?php
    endif; ?>
            <?php if (!empty($_SESSION['p_empresa'])): ?><li><a href="index.php?action=admin_company" class="<?php echo $currentAction === 'admin_company' ? 'active' : ''; ?>"><i class="fas fa-building"></i> Empresa</a></li><?php
    endif; ?>
        </ul>
        <?php
endif; ?>

    </nav>
    <div class="sidebar-footer">
        <button id="themeToggle" class="sidebar-theme-toggle" style="display:flex; align-items:center; justify-content:center; gap:10px; width:100%; padding:12px 15px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:#94A3B8; font-size:13px; font-weight:600; cursor:pointer; transition:all 0.3s ease;">
            <i class="fas fa-moon" id="themeIcon"></i> <span id="themeText">Modo Oscuro</span>
        </button>
    </div>
</aside>

<script>
    // JS Logic for Sidebar Dropdowns
    document.addEventListener("DOMContentLoaded", function() {
        const toggleButtons = document.querySelectorAll('.menu-module-toggle');
        const storageKey = 'kavak_sidebar_expanded';
        
        let savedState = null;
        try {
            savedState = JSON.parse(localStorage.getItem(storageKey));
        } catch(e) {}

        toggleButtons.forEach((btn, index) => {
            let submenu = btn.nextElementSibling;
            
            // Restore visual state
            if (savedState !== null) {
                if (savedState.includes(index)) {
                    btn.classList.add('open');
                    if (submenu && submenu.classList.contains('submenu-list')) {
                        submenu.classList.add('open');
                    }
                } else {
                    btn.classList.remove('open');
                    if (submenu && submenu.classList.contains('submenu-list')) {
                        submenu.classList.remove('open');
                    }
                }
            }

            // Always ensure the active module is open regardless of saved state
            if (submenu && submenu.classList.contains('submenu-list')) {
                if (submenu.querySelector('a.active')) {
                    btn.classList.add('open');
                    submenu.classList.add('open');
                }
            }

            btn.addEventListener('click', function() {
                this.classList.toggle('open');
                let sub = this.nextElementSibling;
                if (sub && sub.classList.contains('submenu-list')) {
                    sub.classList.toggle('open');
                }
                
                // Save state
                let currentExpanded = [];
                document.querySelectorAll('.menu-module-toggle').forEach((b, i) => {
                    if (b.classList.contains('open')) currentExpanded.push(i);
                });
                localStorage.setItem(storageKey, JSON.stringify(currentExpanded));
            });
        });
        
        // Sync the state on load (after enforcing the active module rule)
        let finalExpanded = [];
        document.querySelectorAll('.menu-module-toggle').forEach((b, i) => {
            if (b.classList.contains('open')) finalExpanded.push(i);
        });
        localStorage.setItem(storageKey, JSON.stringify(finalExpanded));
    });
</script>
