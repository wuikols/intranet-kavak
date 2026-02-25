<?php
global $globalQuickLinks;
if (empty($_SESSION['user_id'])) {
    header("Location: index.php?action=login");
    exit;
}

require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/QuickLink.php';

$db = (new Database())->getConnection();
$quickLinksList = (new QuickLink($db))->getAll();
$currentUser = (new User($db))->getUserById($_SESSION['user_id']);
$foto_perfil = ($currentUser['foto_perfil'] ?? 'default.png') . '?v=' . time();
$rol_nombre = strtoupper($currentUser['rol_nombre'] ?? 'USUARIO');
$isSuperAdmin = strpos($rol_nombre, 'SUPER') !== false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>DyP Upgrade - Buscador</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">

    <style>
        :root {
            --k-blue: #1a73e8;
            --k-bg: #f4f6f9;
            --text-main: #202124;
            --text-sub: #5f6368;
            --border: #e0e0e0;
            --c-permiso: #3b82f6;
            --c-soap: #8b5cf6;
            --c-rt: #f59e0b;
            --c-av: #10b981;
        }

        /* Re-mapping standard body to our dashboard section */
        .dyp-content { font-family: 'Inter', sans-serif; color: var(--text-main); }

        /* Buscador Integrado */
        .search-wrapper-dyp { display: flex; align-items: center; max-width: 600px; width: 100%; margin: 20px auto; }
        .search-capsule {
            background: #fff; border-radius: 50px; 
            padding: 8px 8px 8px 25px; 
            display: flex; align-items: center; justify-content: space-between;
            border: 1px solid #d1d5db; width: 100%;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .search-capsule:focus-within { background: #fff; box-shadow: 0 4px 12px rgba(60,64,67,0.15); border-color: var(--k-blue); }
        .search-input {
            flex: 1; border: none; background: transparent; outline: none; font-size: 16px; 
            font-family: inherit; color: var(--text-main); margin-right: 10px;
        }
        .btn-search-pill {
            background: var(--k-blue); color: #fff; border: none; 
            padding: 10px 28px; border-radius: 40px; 
            font-size: 15px; font-weight: 600; cursor: pointer; 
            transition: 0.2s; flex-shrink: 0;
        }
        .btn-search-pill:hover { background: #1557b0; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }

        /* Contenedor Resultados */
        .dyp-results-container {
            display: none; flex-direction: column; padding: 20px 0;
            max-width: 1020px; width: 100%; margin: 0 auto; 
            animation: fadeIn 0.3s ease-out;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* A. HEADER AUTO */
        .vehicle-header {
            background: #fff; border-radius: 12px; padding: 25px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08); margin-bottom: 20px;
            flex-wrap: wrap; gap: 20px;
        }
        .vh-left { display: flex; align-items: center; gap: 20px; }
        .vh-icon { width: 55px; height: 55px; background: #eff6ff; color: var(--k-blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 30px; }
        .vh-text h2 { margin: 0; font-size: 24px; font-weight: 800; color: #1e293b; }
        .vh-sub { margin-top: 5px; font-size: 15px; color: #64748b; font-weight: 500; display: flex; align-items: center; gap: 10px;}
        .badge-canal { background: #f1f5f9; padding: 4px 12px; border-radius: 6px; color: #334155; font-size: 13px; font-weight: 700; border: 1px solid #cbd5e1; }
        
        .vh-right { display: flex; gap: 15px; }
        .id-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 20px; text-align: center; min-width: 140px; }
        .id-label { font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; }
        .id-val { font-size: 20px; font-weight: 800; color: #0f172a; }

        /* B. TARJETAS (GRID) */
        .cards-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; 
            margin-bottom: 25px; align-items: stretch; 
        }
        @media (max-width: 1100px) { .cards-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .cards-grid { grid-template-columns: 1fr; } }

        .info-card {
            background: #fff; border-radius: 10px; padding: 22px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            border-top: 4px solid transparent;
            display: flex; flex-direction: column; 
            height: 100%; transition: transform 0.2s;
        }
        .info-card:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .bc-blue { border-top-color: var(--c-permiso); }
        .bc-purple { border-top-color: var(--c-soap); }
        .bc-orange { border-top-color: var(--c-rt); }
        .bc-green { border-top-color: var(--c-av); }

        .card-title { font-size: 17px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
        .data-group { display: flex; flex-direction: column; gap: 10px; margin-bottom: 15px;}
        .data-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
        .d-label { color: #475569; font-weight: 600; font-size: 14px; }
        .d-val { color: #0f172a; font-weight: 800; text-align: right; font-size: 15px; }
        .status-row { margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 15px; font-size: 15px; border-top: 1px dashed #e2e8f0; }
        .anotaciones-text { font-size: 13px; color: #64748b; line-height: 1.5; font-weight: 500; margin-bottom: 15px; flex-grow: 1; }

        /* C. OTs */
        .ots-container {
            background: #fff; border-radius: 12px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            display: flex; flex-direction: column;
            border-top: 4px solid var(--k-blue);
        }
        
        .ots-title-bar { padding: 18px 25px; border-bottom: 1px solid #e2e8f0; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px; font-size: 16px;}
        .alert-banner {
            background: #fffbeb; border: 1px solid #fcd34d; color: #92400e;
            padding: 15px 25px; margin: 20px; border-radius: 8px;
            font-size: 15px; display: flex; align-items: center; gap: 12px;
        }
        .alert-banner a { color: var(--k-blue); font-weight: 700; text-decoration: none; }

        .table-scroll-area { overflow-x: auto; padding: 0 20px 20px 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 800px;}
        th { background: #f8fafc; text-align: left; padding: 15px; color: #475569; font-size: 13px; font-weight: 800; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        td { padding: 15px; border-bottom: 1px solid #f1f5f9; color: #334155; font-weight: 600; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .success-center { display: flex; align-items: center; justify-content: center; flex-direction: column; color: #059669; gap: 15px; padding: 50px 20px; text-align: center; }
        .success-pill { background: #ecfdf5; border: 1px solid #6ee7b7; padding: 15px 35px; border-radius: 50px; font-weight: 600; display: flex; align-items: center; gap: 12px; font-size: 16px;}

        /* Badges */
        .badge { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; text-transform: uppercase; display: inline-block;}
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-red { background: #fee2e2; color: #991b1b; }
        .bg-yellow { background: #fef9c3; color: #a16207; }
        .bg-gray { background: #f1f5f9; color: #475569; }

        .spinner-mini { display: none; margin-left:10px; border: 2px solid rgba(255,255,255,0.3); border-top: 2px solid #fff; border-radius: 50%; width: 16px; height: 16px; animation: spin-loader 0.8s linear infinite; }
        @keyframes spin-loader { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Adapting to standard sidebar layout */
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }
        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;}
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
$topbarTitle = 'DyP Upgrade';
include 'partials/topbar.php';
?>

            <section class="content-area dyp-content" style="padding-top: 30px;">
                
                <div class="search-wrapper-dyp">
                    <div class="search-capsule">
                        <span class="material-icons-round" style="color:#94a3b8; font-size: 24px;">search</span>
                        <input type="text" id="searchInput" class="search-input" placeholder="Buscar por Patente o Stock ID (Ej: DEMO123)..." autocomplete="off">
                        <button class="btn-search-pill" id="btnSearch" onclick="doSearch()">Buscar <span class="spinner-mini" id="searchSpinner"></span></button>
                    </div>
                </div>

                <div id="mainDypContainer" class="dyp-results-container">
                    
                    <div class="vehicle-header">
                        <div class="vh-left">
                            <div class="vh-icon"><span class="material-icons-round">directions_car</span></div>
                            <div class="vh-text">
                                <h2 id="lblMarcaModelo">...</h2>
                                <div class="vh-sub">
                                    <span id="lblDetalle">...</span> • 
                                    <span class="badge-canal" id="lblCanal">...</span>
                                </div>
                            </div>
                        </div>
                        <div class="vh-right">
                            <div class="id-card">
                                <div class="id-label">PATENTE</div>
                                <div class="id-val" id="lblPatente">...</div>
                            </div>
                            <div class="id-card">
                                <div class="id-label">STOCK ID</div>
                                <div class="id-val" id="lblStock">...</div>
                            </div>
                        </div>
                    </div>

                    <div class="cards-grid">
                        <div class="info-card bc-blue">
                            <div class="card-title"><i class="material-icons-round" style="color:var(--c-permiso);">description</i> Permiso Circulación</div>
                            <div class="data-group">
                                <div class="data-row"><span class="d-label">Comuna</span><span class="d-val" id="vComuna">-</span></div>
                                <div class="data-row"><span class="d-label">Vencimiento</span><span class="d-val" id="vPermisoDate">-</span></div>
                            </div>
                            <div class="status-row"><span class="d-label">Estado</span><span id="vPermisoStatus"></span></div>
                        </div>
                        <div class="info-card bc-purple">
                            <div class="card-title"><i class="material-icons-round" style="color:var(--c-soap);">health_and_safety</i> SOAP</div>
                            <div class="data-group">
                                <div class="data-row"><span class="d-label">Vencimiento</span><span class="d-val" id="vSoapDate">-</span></div>
                            </div>
                            <div class="status-row"><span class="d-label">Estado</span><span id="vSoapStatus"></span></div>
                        </div>
                        <div class="info-card bc-orange">
                            <div class="card-title"><i class="material-icons-round" style="color:var(--c-rt);">fact_check</i> Revisión Técnica</div>
                            <div class="data-group">
                                <div class="data-row"><span class="d-label">Vencimiento</span><span class="d-val" id="vRtDate">-</span></div>
                            </div>
                            <div class="status-row"><span class="d-label">Estado</span><span id="vRtStatus"></span></div>
                        </div>
                        <div class="info-card bc-green">
                            <div class="card-title"><i class="material-icons-round" style="color:var(--c-av);">gavel</i> Anotaciones Vigentes</div>
                            <div class="anotaciones-text">
                                Indica si el vehículo posee prendas o limitaciones al dominio por parte de una entidad financiera.
                            </div>
                            <div class="status-row"><span class="d-label">Situación</span><span id="vAvStatus"></span></div>
                        </div>
                    </div>

                    <div id="otsContainer" class="ots-container">
                        <div class="ots-title-bar"><span class="material-icons-round" style="color:#3b82f6; font-size:24px;">build_circle</span> Órdenes de Trabajo Pendientes</div>
                        
                        <div id="otsAlert" class="alert-banner" style="display:none;">
                            <span class="material-icons-round" style="font-size:22px;">info</span>
                            <span>La unidad tiene trabajos por realizarse: <strong id="lblOtsCount">0 OTs pendientes</strong>. Subir consulta al canal <a href="#" target="_blank">@solicitudes-hub-operaciones</a></span>
                        </div>

                        <div id="tableScroll" class="table-scroll-area" style="display:none;">
                            <table id="otsTable">
                                <thead>
                                    <tr><th>Proceso</th><th>Área Taller</th><th>Gestión</th><th>Tipo Trabajo</th><th>Taller</th><th>Estado</th></tr>
                                </thead>
                                <tbody id="otsTableBody"></tbody>
                            </table>
                        </div>

                        <div id="msgSuccess" class="success-center" style="display:none;">
                            <div class="success-pill">
                                <span class="material-icons-round" style="font-size:24px;">check_circle</span>
                                La unidad se encuentra al día, sin trabajos pendientes u otra observación.
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        const searchInput = document.getElementById('searchInput');
        const btnSearch = document.getElementById('btnSearch');
        const spinner = document.getElementById('searchSpinner');

        searchInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') doSearch(); });

        function doSearch() {
            const q = searchInput.value.trim().toUpperCase();
            if(!q) return;
            
            btnSearch.disabled = true;
            spinner.style.display = 'inline-block';
            
            fetch('index.php?action=ajax_dyp_search', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'stock_id=' + encodeURIComponent(q)
            })
            .then(res => res.json())
            .then(data => {
                btnSearch.disabled = false;
                spinner.style.display = 'none';
                renderData(data);
            })
            .catch(err => {
                btnSearch.disabled = false;
                spinner.style.display = 'none';
                alert("Error de conexión al servidor.");
            });
        }

        function renderData(res) {
            if(res.error) {
                alert(res.error); 
                document.getElementById('mainDypContainer').style.display = 'none';
                return; 
            }
            if(!res.found) { 
                alert("Vehículo no encontrado en los registros."); 
                document.getElementById('mainDypContainer').style.display = 'none';
                return; 
            }

            document.getElementById('mainDypContainer').style.display = 'flex';

            // Header
            let vh = res.vehiculo || {};
            document.getElementById('lblMarcaModelo').innerText = (vh.marca||"") + " " + (vh.modelo||"");
            document.getElementById('lblDetalle').innerText = (vh.version||"") + " | " + (vh.ano||"");
            document.getElementById('lblCanal').innerText = res.documentos ? res.documentos.canal : "-";
            document.getElementById('lblPatente').innerText = res.patente ? res.patente.replace(/[^A-Z0-9]/gi,'') : "-";
            document.getElementById('lblStock').innerText = res.stockId || "-";

            // Tarjetas
            let doc = res.documentos || { permiso:{}, soap:{}, revision:{} };
            document.getElementById('vComuna').innerText = doc.permiso.comuna || "-";
            document.getElementById('vPermisoDate').innerText = doc.permiso.vencimiento || "-";
            document.getElementById('vPermisoStatus').innerHTML = getBadge(doc.permiso.estado);

            document.getElementById('vSoapDate').innerText = doc.soap.vencimiento || "-";
            document.getElementById('vSoapStatus').innerHTML = getBadge(doc.soap.estado);

            document.getElementById('vRtDate').innerText = doc.revision.vencimiento || "-";
            document.getElementById('vRtStatus').innerHTML = getBadge(doc.revision.estado);

            // Anotaciones
            let avRaw = res.prenda ? res.prenda.estado : "-";
            document.getElementById('vAvStatus').innerHTML = getBadge(avRaw, 'av');

            // OTs
            let ots = res.ots || [];
            const otsAlert = document.getElementById('otsAlert');
            const tableScroll = document.getElementById('tableScroll');
            const msgSuccess = document.getElementById('msgSuccess');
            const tbody = document.getElementById('otsTableBody');

            if(ots.length > 0) {
                otsAlert.style.display = 'flex';
                tableScroll.style.display = 'block';
                msgSuccess.style.display = 'none';
                document.getElementById('lblOtsCount').innerText = ots.length + " OTs pendientes";
                let rows = "";
                ots.forEach(ot => {
                    rows += `<tr>
                        <td style="font-weight:700;">${ot.proceso}</td>
                        <td>${ot.area}</td>
                        <td style="color:var(--text-sub);">${ot.gestion}</td>
                        <td>${ot.tipoTrabajo}</td>
                        <td>${ot.tallerInfo}</td>
                        <td>${getBadge(ot.estadoTrabajo)}</td>
                    </tr>`;
                });
                tbody.innerHTML = rows;
            } else {
                otsAlert.style.display = 'none';
                tableScroll.style.display = 'none';
                msgSuccess.style.display = 'flex';
            }
        }

        function getBadge(status, type) {
            if(!status || status==="-") return `<span class="badge bg-gray">-</span>`;
            let s = status.toString().toUpperCase();
            
            if(type === 'av') {
                if(s.includes('LIBRE') || s.includes('OK')) return `<span class="badge bg-green"><i class="fas fa-check-circle" style="margin-right:5px;"></i> SIN PRENDA</span>`;
                return `<span class="badge bg-red"><i class="fas fa-times-circle" style="margin-right:5px;"></i> PRENDADO</span>`;
            }
            if(s.includes('VIGENTE') || s.includes('OK') || s.includes('LIBRE') || s.includes('TERMINADO')) return `<span class="badge bg-green">${s}</span>`;
            if(s.includes('PENDIENTE')) return `<span class="badge bg-yellow">${s}</span>`;
            if(s.includes('VENCIDO') || s.includes('RECHAZADO') || s.includes('PRENDADO')) return `<span class="badge bg-red">${s}</span>`;
            return `<span class="badge bg-gray">${s}</span>`;
        }
    </script>
</body>
</html>
