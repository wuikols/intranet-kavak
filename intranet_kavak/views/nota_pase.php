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

$topbarTitle = 'Generador - Nota de Pase';
$topbarBadge = 'Comercial';
$topbarBadgeClass = 'badge-role';
$topbarBadgeStyle = 'background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2);';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Generador - Nota de Pase</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <link rel="stylesheet" href="/intranet_kavak/assets/css/style.css?v=<?php echo time(); ?>">

    <style>
        :root { --primary-color: #0056b3; --success-color: #28a745; --bg-color-cot: #f4f4f9; }
        .oculto { display: none !important; }

        /* ESTILOS NOTA DE PASE */
        .generador-layout { display: flex; gap: 20px; flex-direction: row; align-items: flex-start; width: 100%; max-width: 1100px; margin: 0 auto; font-family: 'Inter', sans-serif;}
        .gen-form-container { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); flex: 1; min-width: 0; }
        
        .gen-result-container { flex: 1; display: flex; flex-direction: column; gap: 20px; max-width: 500px; position: sticky; top: 20px; min-width: 0; }
        
        .gen-h2 { text-align: center; text-transform: uppercase; margin-bottom: 25px; color: #333; border-bottom: 2px solid #eee; padding-bottom: 15px; font-size: 1.25em; font-weight: 700; }
        .gen-form-row { display: flex; border-bottom: 1px solid #eee; align-items: center; padding: 8px 0; width: 100%; }
        .gen-form-row:last-child { border-bottom: none; }
        .gen-label-col { flex: 0 0 40%; padding: 10px; font-weight: 600; background-color: #f9f9f9; border-right: 1px solid #eee; align-self: stretch; display: flex; align-items: center; font-size: 13px; word-wrap: break-word; }
        .gen-input-col { flex: 1; padding: 5px 10px; min-width: 0; }
        .gen-input-col select, .gen-input-col input, .gen-input-col textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        .gen-input-col textarea { resize: vertical; height: 60px; }
        .btn-modal-trigger { background-color: #ff9800; color: white; border: none; padding: 10px; width: 100%; border-radius: 4px; cursor: pointer; font-weight: bold; margin-top: 5px; }
        .generate-btn { background-color: var(--primary-color); color: white; border: none; padding: 15px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; width: 100%; }
        .copy-btn { background-color: var(--success-color); color: white; border: none; padding: 15px; font-size: 16px; font-weight: bold; border-radius: 5px; cursor: pointer; width: 100%; transition: all 0.3s ease;}
        #output-text { background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; white-space: pre-wrap; font-family: monospace; min-height: 200px; font-size: 13px; width: 100%; word-break: break-all; }
        
        /* ESTILOS MODAL VARIABLES */
        .gen-modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .gen-modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border: 1px solid #888; width: 90%; max-width: 500px; border-radius: 8px; }
        .gen-modal-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; }
        .gen-close-modal { font-size: 28px; cursor: pointer; }
        .gen-modal-section { background: #f9f9f9; padding: 10px; border-radius: 4px; border-left: 4px solid var(--primary-color); margin-bottom: 10px; }
        .radio-option { display: block; margin: 8px 0; font-size: 15px; cursor: pointer; }
        .btn-save-modal { background-color: var(--success-color); color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 10px;}

        @media (max-width: 900px) { .generador-layout { flex-direction: column; } .gen-form-container, .gen-result-container { width: 100%; max-width: 100%; } .gen-result-container { position: static; } }
        @media (max-width: 600px) { .gen-form-row { flex-direction: column; align-items: stretch; } .gen-label-col { width: 100%; border-right: none; margin-bottom: 5px; } }

        /* Adapting to standard sidebar layout */
        .sidebar-footer { margin-top: auto; padding: 20px; border-top: 1px solid rgba(255,255,255,0.05); }
        .sidebar-theme-toggle { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; color: #94A3B8; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
        .sidebar-theme-toggle:hover { background: rgba(255,255,255,0.1); color: #ffffff; transform: translateY(-2px); }
        .topbar-extended { display: flex; justify-content: space-between; align-items: center; width: 100%; }
        .quick-links-container { display: flex; align-items: center; gap: 10px; margin-left: 30px; border-left: 2px solid var(--border-subtle); padding-left: 20px; }
        @media(max-width:1024px){ .quick-links-container {display:none;} }
        .ql-btn { width: 36px; height: 36px; background: var(--input-bg); border: 1px solid var(--border-subtle); border-radius: 8px; display: flex; justify-content: center; align-items: center; color: var(--text-secondary); text-decoration: none; transition: 0.2s; position: relative;}
        .ql-btn:hover { background: var(--card-bg); color: var(--accent-color); border-color: var(--accent-color); transform: translateY(-2px); box-shadow: var(--shadow-sm);}
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body onload="initAll()">
    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php
include 'partials/topbar.php';
?>

            <section class="content-area" style="padding-top: 20px;">
                <div class="generador-layout">
                    <form class="gen-form-container" id="miFormularioNota">
                        <h2 class="gen-h2">NOTA DE PASE</h2>
                        
                        <div class="gen-form-row">
                            <div class="gen-label-col">Cita</div>
                            <div class="gen-input-col">
                                <select id="cita" onchange="actualizarVisibilidadNota()">
                                    <option value="" selected>Seleccione...</option>
                                    <option value="Reserva in HUB">Reserva in HUB</option>
                                    <option value="Reserva Previa">Reserva Previa</option>
                                    <option value="Reserventa">Reserventa</option>
                                    <option value="No reserva">No reserva</option>
                                </select>
                            </div>
                        </div>

                        <div id="bloque-no-reserva" class="oculto">
                            <div class="gen-form-row">
                                <div class="gen-label-col">Motivo de No Reserva</div>
                                <div class="gen-input-col"><textarea id="motivoNoReserva" placeholder="Ej: Solo cotizando" rows="2"></textarea></div>
                            </div>
                            <div class="gen-form-row"><div class="gen-label-col">Auto de interés</div><div class="gen-input-col"><input type="text" id="autoInteres" placeholder="Ej: Nissan Kicks"></div></div>
                            <div class="gen-form-row">
                                <div class="gen-label-col">Cliente Caliente</div>
                                <div class="gen-input-col">
                                    <select id="clienteCaliente">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="HOT">HOT</option>
                                        <option value="COLD">COLD</option>
                                    </select>
                                </div>
                            </div>
                            <div class="gen-form-row"><div class="gen-label-col">Fecha posible inversión</div><div class="gen-input-col"><input type="date" id="fechaInversion"></div></div>
                        </div>

                        <div id="bloque-estandar" class="oculto">
                            <div class="gen-form-row"><div class="gen-label-col">Patente</div><div class="gen-input-col"><input type="text" id="patenteNota" placeholder="Ej: XXXX12"></div></div>
                            <div class="gen-form-row"><div class="gen-label-col">Stock ID</div><div class="gen-input-col"><input type="text" id="stockIdNota" placeholder="Ej: 464646"></div></div>
                            
                            <div class="gen-form-row">
                                <div class="gen-label-col">Bonificaciones</div>
                                <div class="gen-input-col">
                                    <select id="bonificaciones" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="gen-form-row oculto" id="row-tipoBono">
                                <div class="gen-label-col">Tipo de Bono</div>
                                <div class="gen-input-col">
                                    <select id="tipoBono" class="form-select" multiple size="6" onchange="actualizarVisibilidadNota()">
                                        <option value="Comercial">Comercial (Variable)</option>
                                        <option value="Reserva Previa">Reserva Previa ($50.000)</option>
                                        <option value="CRM">CRM ($100.000)</option>
                                        <option value="Detailing">Detailing ($50.000)</option>
                                        <option value="Discrecional">Discrecional (Variable)</option>
                                        <option value="Gastos Admin.">Gastos Admin. (%)</option>
                                    </select>
                                    <div style="font-size: 11px; color: #666; margin-top: 4px;">
                                        <i class="bi bi-info-circle"></i> Mantén presionado <strong>Ctrl</strong> (o Cmd) para seleccionar varios.
                                    </div>
                                    <button type="button" id="btnConfigurarVariables" class="btn-modal-trigger oculto" onclick="abrirModalVariables()">⚙️ SELECCIONAR VALORES</button>
                                </div>
                            </div>
                            
                            <div class="gen-form-row oculto" id="row-montoBono">
                                <div class="gen-label-col">Monto total de bono(s)</div>
                                <div class="gen-input-col"><input type="text" id="montoBono" placeholder="Calculando..." readonly></div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Tipo de Auto</div>
                                <div class="gen-input-col">
                                    <select id="tipoAuto" multiple class="form-select">
                                        <option value="Kavak">Kavak</option>
                                        <option value="Outlet">Outlet</option>
                                        <option value="Kasper">Kasper</option>
                                        <option value="0 KM Livan y Zx">0 KM Livan y Zx</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Financia</div>
                                <div class="gen-input-col">
                                    <select id="financia" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="gen-form-row oculto" id="row-financiera">
                                <div class="gen-label-col">Financiera</div>
                                <div class="gen-input-col">
                                    <select id="financiera" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="Global">Global</option>
                                        <option value="Tanner">Tanner</option>
                                        <option value="Santander Consumer">Santander Consumer</option>
                                        <option value="Autofin">Autofin</option>
                                        <option value="Otra">Otra</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gen-form-row oculto" id="row-estadoCredito">
                                <div class="gen-label-col">Estado del crédito</div>
                                <div class="gen-input-col">
                                    <select id="estadoCredito" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="Validado">Validado</option>
                                        <option value="Firmado">Firmado</option>
                                        <option value="Pre Aprobado">Pre Aprobado</option>
                                        <option value="Rechazado">Rechazado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gen-form-row oculto" id="row-financiaGa">
                                <div class="gen-label-col">Financia GA</div>
                                <div class="gen-input-col">
                                    <select id="financiaGa" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gen-form-row oculto" id="row-detalleFinanciaGa">
                                <div class="gen-label-col">Monto GA a Financiar</div>
                                <div class="gen-input-col"><input type="text" id="detalleFinanciaGa" placeholder="$50.000" onkeyup="formatearMonedaInput(this)"></div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Comentarios del pago</div>
                                <div class="gen-input-col"><textarea id="comentariosPago" placeholder="Ej: Traerá Vale Vista"></textarea></div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Hallazgos</div>
                                <div class="gen-input-col">
                                    <select id="hallazgos" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="gen-form-row oculto" id="row-detallarHallazgos">
                                <div class="gen-label-col">Detalle Hallazgos</div>
                                <div class="gen-input-col"><input type="text" id="detallarHallazgos" placeholder="Ej: Rayón puerta"></div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Upgrade</div>
                                <div class="gen-input-col">
                                    <select id="upgradeEntrega" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gen-form-row oculto" id="row-valorUpgrade">
                                <div class="gen-label-col">Valor Total</div>
                                <div class="gen-input-col"><input type="text" id="valorUpgrade" placeholder="$ 50.000" onkeyup="formatearMonedaInput(this)"></div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Kavak Total</div>
                                <div class="gen-input-col">
                                    <select id="ktLite" onchange="actualizarVisibilidadNota()">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="KT Lite">KT Lite</option>
                                        <option value="KT Premium">KT Premium</option>
                                        <option value="No Contrata">No Contrata</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gen-form-row oculto" id="row-motivoRechazoKt">
                                <div class="gen-label-col">Motivo rechazo</div>
                                <div class="gen-input-col"><input type="text" id="motivoRechazoKt" placeholder="Motivo"></div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Trade in / Permuta</div>
                                <div class="gen-input-col">
                                    <select id="tradeIn">
                                        <option value="" selected>Seleccione...</option>
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </div>
                            </div>

                            <div class="gen-form-row">
                                <div class="gen-label-col">Comentarios:</div>
                                <div class="gen-input-col"><textarea id="comentariosRelevantes" placeholder="Observaciones..."></textarea></div>
                            </div>
                        </div>
                    </form>

                    <div class="gen-result-container">
                        <button type="button" class="generate-btn" onclick="generarTexto()">GENERAR TEXTO</button>
                        <div id="output-text">El texto aparecerá aquí...</div>
                        <button type="button" class="copy-btn" id="btnCopiar" onclick="copiarTexto()">COPIAR AL PORTAPAPELES</button>
                    </div>
                </div>

                <div id="modalVariables" class="gen-modal">
                    <div class="gen-modal-content">
                        <div class="gen-modal-header">
                            <h3>Configurar Bonos Variables</h3>
                            <span class="gen-close-modal" onclick="cerrarModalVariables()">×</span>
                        </div>
                        
                        <div id="modal-sec-comercial" class="gen-modal-section oculto">
                            <h4>Bono Comercial</h4>
                            <label class="radio-option"><input type="radio" name="m_comercial" value="100000" checked> $100.000</label>
                            <label class="radio-option"><input type="radio" name="m_comercial" value="200000"> $200.000</label>
                            <label class="radio-option"><input type="radio" name="m_comercial" value="300000"> $300.000</label>
                            <label class="radio-option"><input type="radio" name="m_comercial" value="400000"> $400.000</label>
                        </div>
                        
                        <div id="modal-sec-discrecional" class="gen-modal-section oculto">
                            <h4>Bono Discrecional</h4>
                            <label class="radio-option"><input type="radio" name="m_discrecional" value="100000" checked> $100.000</label>
                            <label class="radio-option"><input type="radio" name="m_discrecional" value="200000"> $200.000</label>
                            <label class="radio-option"><input type="radio" name="m_discrecional" value="300000"> $300.000</label>
                            <label class="radio-option"><input type="radio" name="m_discrecional" value="400000"> $400.000</label>
                        </div>
                        
                        <div id="modal-sec-gastos" class="gen-modal-section oculto">
                            <h4>% Gastos Admin</h4>
                            <label class="radio-option"><input type="radio" name="m_gastos" value="12,5%" checked> 12,5%</label>
                            <label class="radio-option"><input type="radio" name="m_gastos" value="25%"> 25%</label>
                            <label class="radio-option"><input type="radio" name="m_gastos" value="50%"> 50%</label>
                            <label class="radio-option"><input type="radio" name="m_gastos" value="100%"> 100%</label>
                        </div>
                        
                        <button type="button" class="btn-save-modal" onclick="guardarVariables()">GUARDAR Y CALCULAR</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- MAIN APP SCRIPT FOR SIDEBAR -->
    <script src="/intranet_kavak/assets/js/main.js?v=<?php echo time(); ?>"></script>

    <script>
        const formatCLP = (num) => new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(num);

        function initAll() {
            // Inicializar estados visuales de la interfaz
            actualizarVisibilidadNota();
            
            // Listeners para validación de patentes
            const patenteInput = document.getElementById('patenteNota');
            if (patenteInput) {
                patenteInput.addEventListener('blur', function() {
                    let val = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                    this.value = val;
                    
                    const regexAntigua = /^[A-Z]{2}\d{4}$/;
                    const regexNueva = /^[A-Z]{4}\d{2}$/;

                    if(val && !regexAntigua.test(val) && !regexNueva.test(val)) {
                        alert("⚠️ Formato de patente inválido (Debe ser AA1234 o BBBB12).");
                        this.value = ""; 
                    }
                });
            }
        }

        function formatearMonedaInput(input) {
            let val = input.value.replace(/\D/g, '');
            if (val) {
                input.value = '$ ' + parseInt(val, 10).toLocaleString('es-CL');
            } else {
                input.value = '';
            }
        }

        function actualizarVisibilidadNota() {
             const citaVal = document.getElementById('cita').value;
             const bloqueNo = document.getElementById('bloque-no-reserva');
             const bloqueStd = document.getElementById('bloque-estandar');
             
             if(citaVal === "") { 
                 bloqueNo.classList.add('oculto'); 
                 bloqueStd.classList.add('oculto'); 
                 return; 
             }
             
             if(citaVal === 'No reserva') {
                 bloqueNo.classList.remove('oculto'); 
                 bloqueStd.classList.add('oculto');
                 if(!document.getElementById('fechaInversion').value) {
                     document.getElementById('fechaInversion').valueAsDate = new Date();
                 }
             } else {
                 bloqueNo.classList.add('oculto'); 
                 bloqueStd.classList.remove('oculto');
                 
                 const b = document.getElementById('bonificaciones').value;
                 document.getElementById('row-tipoBono').classList.toggle('oculto', b !== 'Si');
                 document.getElementById('row-montoBono').classList.toggle('oculto', b !== 'Si');
                 
                 if(b === 'Si') {
                    const sel = Array.from(document.getElementById('tipoBono').selectedOptions).map(o=>o.value);
                    const btn = document.getElementById('btnConfigurarVariables');
                    if(sel.includes('Comercial')||sel.includes('Discrecional')||sel.includes('Gastos Admin.')) {
                        btn.classList.remove('oculto'); 
                    } else { 
                        btn.classList.add('oculto'); 
                        guardarVariables(); 
                    }
                 } else { 
                     document.getElementById('montoBono').value = ""; 
                 }
                 
                 // --- LÓGICA FINANCIAMIENTO EN CASCADA ---
                 const f = document.getElementById('financia').value;
                 const financieraVal = document.getElementById('financiera').value;
                 const estadoVal = document.getElementById('estadoCredito').value;

                 // 1. Mostrar Financiera si Financia = Si
                 const rowFinanciera = document.getElementById('row-financiera');
                 if (f === 'Si') { 
                     rowFinanciera.classList.remove('oculto'); 
                 } else { 
                     rowFinanciera.classList.add('oculto'); 
                     document.getElementById('row-estadoCredito').classList.add('oculto');
                     document.getElementById('row-financiaGa').classList.add('oculto');
                     document.getElementById('row-detalleFinanciaGa').classList.add('oculto');
                     return; 
                 }

                 // 2. Mostrar Estado del Crédito si se eligió Financiera
                 const rowEstado = document.getElementById('row-estadoCredito');
                 if (f === 'Si' && financieraVal && financieraVal !== "") {
                     rowEstado.classList.remove('oculto');
                 } else {
                     rowEstado.classList.add('oculto');
                     document.getElementById('row-financiaGa').classList.add('oculto');
                     document.getElementById('row-detalleFinanciaGa').classList.add('oculto');
                     return;
                 }

                 // 3. Mostrar Financia GA si se eligió Estado
                 const rowFinanciaGa = document.getElementById('row-financiaGa');
                 if (f === 'Si' && financieraVal && estadoVal && estadoVal !== "") {
                     rowFinanciaGa.classList.remove('oculto');
                 } else {
                     rowFinanciaGa.classList.add('oculto');
                     document.getElementById('row-detalleFinanciaGa').classList.add('oculto');
                     return;
                 }
                 
                 // 4. Mostrar Detalle GA si Financia GA = Si
                 const fga = document.getElementById('financiaGa').value;
                 document.getElementById('row-detalleFinanciaGa').classList.toggle('oculto', !(fga==='Si'));

                 // --- OTROS CAMPOS DE CONDICIÓN DIRECTA ---
                 document.getElementById('row-detallarHallazgos').classList.toggle('oculto', document.getElementById('hallazgos').value !== 'Si');
                 document.getElementById('row-valorUpgrade').classList.toggle('oculto', document.getElementById('upgradeEntrega').value !== 'Si');
                 document.getElementById('row-motivoRechazoKt').classList.toggle('oculto', document.getElementById('ktLite').value !== 'No Contrata');
             }
        }

        function abrirModalVariables() { 
            document.getElementById('modalVariables').style.display = "block"; 
            const sel = Array.from(document.getElementById('tipoBono').selectedOptions).map(o=>o.value);
            document.getElementById('modal-sec-comercial').classList.toggle('oculto', !sel.includes('Comercial'));
            document.getElementById('modal-sec-discrecional').classList.toggle('oculto', !sel.includes('Discrecional'));
            document.getElementById('modal-sec-gastos').classList.toggle('oculto', !sel.includes('Gastos Admin.'));
        }

        function cerrarModalVariables() { 
            document.getElementById('modalVariables').style.display = "none"; 
        }
        
        function guardarVariables() {
             const sel = Array.from(document.getElementById('tipoBono').selectedOptions).map(o=>o.value);
             let t = 0; 
             let txtGasto = "";
             
             if(sel.includes('Reserva Previa')) t+=50000;
             if(sel.includes('CRM')) t+=100000;
             if(sel.includes('Detailing')) t+=50000;
             if(sel.includes('Comercial')) { 
                 const c = document.querySelector('input[name="m_comercial"]:checked'); 
                 if(c) t+=parseInt(c.value); 
             }
             if(sel.includes('Discrecional')) { 
                 const d = document.querySelector('input[name="m_discrecional"]:checked'); 
                 if(d) t+=parseInt(d.value); 
             }
             if(sel.includes('Gastos Admin.')) { 
                 const g = document.querySelector('input[name="m_gastos"]:checked'); 
                 if(g) txtGasto = " / " + g.value + " Dscto Gasto Admin"; 
             }
             
             let finalTxt = ""; 
             if(t > 0) finalTxt = formatCLP(t); 
             if(t === 0 && txtGasto !== "") finalTxt = "$ 0";
             
             document.getElementById('montoBono').value = finalTxt + txtGasto;
             cerrarModalVariables();
        }

        function generarTexto() {
            const cita = document.getElementById('cita').value;
            let txt = `• CITA: ${cita}\n`;
            
            if(cita === 'No reserva') {
                txt += `• MOTIVO: ${document.getElementById('motivoNoReserva').value}\n• AUTO: ${document.getElementById('autoInteres').value}\n• CLIENTE CALIENTE: ${document.getElementById('clienteCaliente').value}\n• FECHA: ${document.getElementById('fechaInversion').value}\n`;
            } else if (cita !== "") {
                txt += `• PATENTE/STOCK: ${document.getElementById('patenteNota').value} / ${document.getElementById('stockIdNota').value}\n`;
                
                const b = document.getElementById('bonificaciones').value;
                let infoBonos = b;
                if (b === 'Si') {
                    const sel = Array.from(document.getElementById('tipoBono').selectedOptions).map(o=>o.value);
                    let detalleList = [];
                    sel.forEach(val => {
                        let monto = "";
                        if(val === 'Reserva Previa') monto = "$50.000"; 
                        else if(val === 'CRM') monto = "$100.000"; 
                        else if(val === 'Detailing') monto = "$50.000";
                        else if(val === 'Comercial') { const c = document.querySelector('input[name="m_comercial"]:checked'); if(c) monto = formatCLP(parseInt(c.value)); }
                        else if(val === 'Discrecional') { const d = document.querySelector('input[name="m_discrecional"]:checked'); if(d) monto = formatCLP(parseInt(d.value)); }
                        else if(val === 'Gastos Admin.') { const g = document.querySelector('input[name="m_gastos"]:checked'); if(g) monto = g.value + " Dscto"; }
                        
                        if(monto) detalleList.push(`  - ${val} (${monto})`); 
                        else detalleList.push(`  - ${val}`);
                    });
                    if(detalleList.length > 0) { infoBonos += "\n" + detalleList.join("\n"); }
                    infoBonos += `\n  - Total Bonificaciones: ${document.getElementById('montoBono').value}`;
                }
                txt += `• BONOS: ${infoBonos}`;
                
                const tipos = Array.from(document.getElementById('tipoAuto').selectedOptions).map(o=>o.value);
                txt += `\n• TIPO AUTO: ${tipos.join(', ')}\n`;
                
                const f = document.getElementById('financia').value;
                txt += `• FINANCIA: ${f}\n`;
                
                // --- INCLUIR FINANCIERA EN EL TEXTO ---
                if(f==='Si') { 
                    txt += `    Financiera: ${document.getElementById('financiera').value}\n`;
                    txt += `    Estado: ${document.getElementById('estadoCredito').value}\n`; 
                    if(document.getElementById('financiaGa').value === 'Si') {
                        txt += `    Financia GA: Si (${document.getElementById('detalleFinanciaGa').value})\n`; 
                    }
                }
                
                txt += `    Comentarios Pago: ${document.getElementById('comentariosPago').value}\n`;
                txt += `• HALLAZGOS: ${document.getElementById('hallazgos').value} ${document.getElementById('detallarHallazgos').value}\n`;
                txt += `• UPGRADE: ${document.getElementById('upgradeEntrega').value} ${document.getElementById('valorUpgrade').value}\n`;
                txt += `• KAVAK TOTAL: ${document.getElementById('ktLite').value} ${document.getElementById('motivoRechazoKt').value}\n`;
                txt += `• TRADE IN: ${document.getElementById('tradeIn').value}\n`;
                txt += `• COMENTARIOS: ${document.getElementById('comentariosRelevantes').value}\n`;
            }
            document.getElementById('output-text').textContent = txt;
        }

        function copiarTexto() {
            const txt = document.getElementById('output-text').textContent;
            navigator.clipboard.writeText(txt).then(() => { 
                const b = document.getElementById('btnCopiar'); 
                b.innerText = "¡COPIADO!"; 
                b.style.background = "#1e7e34"; 
                
                setTimeout(() => { 
                    b.innerText="COPIAR AL PORTAPAPELES"; 
                    b.style.background=""; 
                }, 2000); 
                
                setTimeout(() => { 
                    document.getElementById('miFormularioNota').reset(); 
                    document.getElementById('output-text').textContent = "El texto aparecerá aquí..."; 
                    document.getElementById('montoBono').value = ""; 
                    actualizarVisibilidadNota(); 
                }, 5000);
            }); 
        }
    </script>
</body>
</html>
