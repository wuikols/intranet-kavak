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
    <title>Cotizador Kavak</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="/intranet_kavak/assets/css/style.css?v=<?php echo time(); ?>">

    <style>
        :root { --primary-color: #0056b3; --success-color: #28a745; --purple-color: #4c0bce; --bg-color-cot: #f4f4f9; }
        .oculto { display: none !important; }

        /* ESTILOS SIMULADOR */
        .simulador-layout { display: flex; gap: 20px; width: 100%; max-width: 1100px; align-items: flex-start; flex-wrap: wrap; margin: 0 auto; font-family: 'Inter', sans-serif;}
        .sim-col-half { display: flex; flex: 1 1 350px; width: 100%; min-width: 300px; }
        .sim-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 100%; display: flex; flex-direction: column; }
        .sim-h2 { text-align: center; color: #333; margin-top: 0; font-size: 16px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #eee; padding-bottom: 15px; margin-bottom: 20px; letter-spacing: 0.5px; }
        .sim-input-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .sim-label { display: block; margin-bottom: 6px; color: #555; font-size: 13px; font-weight: 700; }
        .sim-input, .sim-select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; color: #333; box-sizing: border-box; }
        .sim-input:disabled { background-color: #e9ecef; cursor: not-allowed; font-weight: 600; }
        
        .sim-checkbox-row { margin-bottom: 10px; display: flex; align-items: center; padding: 10px 12px; border-radius: 8px; border: 1px solid #eee; background-color: #f8f9fa; }
        .sim-checkbox-row input[type="checkbox"] { margin-right: 12px; transform: scale(1.3); cursor: pointer; }
        .sim-checkbox-row label { margin-bottom: 0; cursor: pointer; flex: 1; font-size: 13px; }
        
        .sim-pie-row { display: flex; gap: 10px; align-items: flex-end; margin-bottom: 15px; }
        .sim-pie-s { flex: 0 0 80px; } .sim-pie-m { flex: 1; }
        .sim-pie-chk-excepcion { flex: 0 0 auto; height: 43px; display: flex; align-items: center; padding: 0 12px; border: 2px dashed #ff9800; border-radius: 6px; background-color: #fff3cd; margin-bottom: 0 !important; }
        .sim-pie-chk-excepcion input { margin-right: 8px; }

        .sim-box-highlight { display: flex; flex-direction: column; padding: 10px 15px; border: 1px solid #ffcc80; border-radius: 8px; background-color: #fff0e0; margin-bottom: 15px; }
        .sim-radio-option { display: flex; align-items: flex-start; margin-bottom: 8px; font-size: 13px; color: #333; }
        .sim-radio-option input { margin-top: 3px; margin-right: 10px; transform: scale(1.2); }
        
        .sim-percent-wrapper { position: relative; }
        .sim-percent-wrapper input { padding-right: 25px; text-align: right; }
        .sim-percent-symbol { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #666; font-weight: bold; }
        
        .sim-results { border: 1px solid #d1d1d1; border-radius: 10px; padding: 20px; background-color: #fafafa; }
        .sim-divider { height: 1px; background-color: #ddd; margin: 12px 0; }
        .sim-subtitle { font-size: 12px; color: #888; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; margin-top: 5px; }
        
        .sim-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; flex-wrap: wrap; }
        .sim-val { font-weight: 700; white-space: nowrap; }
        
        .c-green { color: var(--success-color); } 
        .c-blue { color: var(--primary-color); } 
        .c-purple { color: var(--purple-color); font-weight: 800; } 
        .c-red { color: #dc3545; }
        
        .sim-summary-box { background-color: #f0f4ff; border: 2px solid var(--purple-color); padding: 15px; border-radius: 8px; margin-top: 15px; }
        .sim-sub-gastos { background-color: #e8f5e9; border: 1px dashed #28a745; padding: 10px; border-radius: 6px; margin: 10px 0; }
        .sim-total-auto { background-color: #fff0f0; border: 1px dashed #dc3545; padding: 10px; border-radius: 6px; margin: 10px 0; display: flex; justify-content: space-between; font-size: 14px; align-items: center; }
        .sim-valor-auto { background-color: #ede7f6; border: 1px solid #d1c4e9; border-radius: 8px; padding: 12px; margin-bottom: 15px; color: #4527a0; }
        
        .sim-sub-item { font-size: 13px; color: #666; display: flex; justify-content: space-between; margin-bottom: 4px; }
        .sim-sub-detail { font-size: 12px; color: #dc3545; display: flex; justify-content: space-between; margin-bottom: 6px; padding-left: 15px; font-style: italic; }
        
        .sim-btn-container { display: flex; gap: 10px; margin-top: 20px; }
        .btn-clear { flex: 1; padding: 15px; background-color: #ff6b6b; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 14px; margin-top:0 !important; }
        .btn-pdf-sim { flex: 1; padding: 15px; background-color: #343a40; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-pdf-sim:hover { background-color: #23272b; cursor: pointer;}
        
        .search-cont { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .search-row { display: flex; gap: 10px; align-items: center;}
        .btn-search { padding: 12px 20px; background: var(--primary-color); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        .btn-search:hover { background: #004494; }

        .header-toggle { display: flex; justify-content: space-between; width: 100%; align-items: center; cursor: pointer; padding: 5px 0; }
        .rotate-up { transform: rotate(180deg); }
        .spinner { display: none; margin-left: 8px; border: 2px solid #f3f3f3; border-top: 2px solid #fff; border-radius: 50%; width: 14px; height: 14px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

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
$topbarTitle = 'Cotizador Kavak';
include 'partials/topbar.php';
?>

            <section class="content-area" style="padding-top: 20px;">
                <div class="simulador-layout">
                    <div class="sim-col-half">
                        <div class="sim-card">
                            <h2 class="sim-h2">Configuración del Crédito</h2>
                            
                            <div class="search-cont">
                                <label class="sim-label" for="txtStockId">Buscar por Stock ID</label>
                                <div class="search-row">
                                    <input class="sim-input" type="text" id="txtStockId" placeholder="Ingrese ID (Ej: 12345)" inputmode="numeric" onkeypress="handleEnter(event)">
                                    <button class="btn-search" id="btnBuscar" onclick="buscarStock()">Buscar <span class="spinner" id="searchSpinner"></span></button>
                                </div>
                                <div id="searchMsg" style="font-size: 12px; margin-top: 5px; font-weight: bold;"></div>
                            </div>

                            <div class="sim-box-highlight" style="padding: 10px;">
                                <div class="header-toggle" style="display: flex; align-items: center; justify-content: space-between;" onclick="toggleTradeInStateFromHeader()">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <input type="checkbox" id="checkEnableTradeIn" style="transform: scale(1.3); cursor: pointer;" onclick="event.stopPropagation(); toggleTradeInState()">
                                        <label for="checkEnableTradeIn" class="sim-label" style="color: #d35400; cursor: pointer; margin:0;">Trade In / Retoma</label>
                                    </div>
                                    <div class="toggle-arrow" id="arrowTradeIn">▼</div>
                                </div>
                                <div id="seccionTradeIn" style="display: none; margin-top: 15px; border-top: 1px solid #ffcc80; padding-top: 10px;">
                                    <div style="margin-bottom: 10px;">
                                        <input class="sim-input" type="text" id="montoTradeIn" inputmode="numeric" placeholder="Monto Retoma ($ 0)" style="font-weight: bold; color: #d35400;">
                                    </div>
                                    <div class="sim-checkbox-row" style="margin-top: 10px; background-color: white;">
                                        <input type="checkbox" id="checkAbonoAdicional" onchange="toggleAbonoInput()">
                                        <label for="checkAbonoAdicional">Dar abono adicional</label>
                                    </div>
                                    <div id="divAbonoAdicional" style="display: none; margin-top: 5px;">
                                        <input class="sim-input" type="text" id="montoAbonoAdicional" placeholder="Monto Adicional ($)" inputmode="numeric">
                                    </div>
                                    <div style="margin-top: 10px;">
                                        <div class="sim-radio-option"><input type="radio" name="tradeInOption" value="1" id="tiOpt1" checked><label for="tiOpt1">1. Usar TODO para Pie (Gastos Aparte)</label></div>
                                        <div class="sim-radio-option"><input type="radio" name="tradeInOption" value="2" id="tiOpt2"><label for="tiOpt2">2. Usar TODO para Pie (Incluye Gastos)</label></div>
                                        <div class="sim-radio-option"><input type="radio" name="tradeInOption" value="3" id="tiOpt3"><label for="tiOpt3">3. Usar hasta Máximo Finan. (Saldo favor)</label></div>
                                    </div>
                                </div>
                            </div>

                            <div class="sim-input-grid">
                                <div>
                                    <label class="sim-label">Precio Contado</label>
                                    <input class="sim-input" type="text" id="precioContado" inputmode="numeric" placeholder="$ 0">
                                </div>
                                <div>
                                    <label class="sim-label">Precio Crédito</label>
                                    <input class="sim-input" type="text" id="precioCredito" inputmode="numeric" placeholder="$ 0">
                                </div>
                            </div>

                            <div class="sim-pie-row">
                                <div class="sim-pie-s">
                                    <label class="sim-label">% Pie</label>
                                    <div class="sim-percent-wrapper">
                                        <input class="sim-input" type="text" id="porcentajePie" inputmode="decimal" placeholder="0">
                                        <span class="sim-percent-symbol">%</span>
                                    </div>
                                </div>
                                <div class="sim-pie-m">
                                    <label class="sim-label">Pie Efectivo ($)</label>
                                    <input class="sim-input" type="text" id="montoPie" inputmode="numeric" placeholder="$ 0">
                                </div>
                                <div class="sim-pie-chk-excepcion">
                                    <input type="checkbox" id="checkExcepcion" onchange="toggleExcepcion()">
                                    <label for="checkExcepcion" style="font-size:13px; cursor: pointer; color: #b75e00;">Excepción 40% pie</label>
                                </div>
                            </div>

                            <div class="sim-checkbox-row" style="background-color: #e3f2fd; border-color: #90caf9;">
                                <input type="checkbox" id="checkPieIncluyeGastos" onchange="toggleIncluyeGastos()">
                                <label for="checkPieIncluyeGastos" style="color: #0d47a1;"><strong>El Monto Pie incluye gastos (Cliente tiene monto fijo)</strong></label>
                            </div>

                            <div class="sim-checkbox-row" style="background-color: #eef2ff; border-color: #d0d7ff; flex-direction: column; align-items: flex-start; padding-bottom: 10px;">
                                <div class="header-toggle" onclick="toggleVisibility('seccionBonosInputs', 'arrowBonos')">
                                    <span class="sim-label" style="margin-bottom:0;">Bonificaciones Especiales</span>
                                    <div class="toggle-arrow" id="arrowBonos">▼</div>
                                </div>
                                <div id="seccionBonosInputs" style="display: none; width: 100%; margin-top: 10px;">
                                    <select id="bonoComercial" class="sim-select" style="margin-bottom: 10px;">
                                        <option value="0">Bono Comercial (Ninguno)</option>
                                        <option value="100000">$100.000</option>
                                        <option value="200000">$200.000</option>
                                        <option value="300000">$300.000</option>
                                        <option value="400000">$400.000</option>
                                    </select>
                                    <div style="display: flex; gap: 8px; margin-bottom: 10px;">
                                        <div class="sim-checkbox-row" style="padding: 8px; margin: 0; flex: 1;"><input type="checkbox" id="bonoReservaPrev"><label>Reserva Prev.</label></div>
                                        <div class="sim-checkbox-row" style="padding: 8px; margin: 0; flex: 1;"><input type="checkbox" id="bonoCRM"><label>CRM</label></div>
                                        <div class="sim-checkbox-row" style="padding: 8px; margin: 0; flex: 1;"><input type="checkbox" id="bonoDetailing"><label>Detailing</label></div>
                                    </div>
                                    <select id="bonoDiscrecional" class="sim-select" style="margin-bottom: 10px;">
                                        <option value="0">Bono Discrecional (Ninguno)</option>
                                        <option value="100000">$100.000</option>
                                        <option value="200000">$200.000</option>
                                        <option value="300000">$300.000</option>
                                    </select>
                                    <select id="bonoGtosAdmin" class="sim-select">
                                        <option value="0">Bono Gastos Administrativos (Ninguno)</option>
                                        <option value="0.125">12,5% desc.</option>
                                        <option value="0.25">25% desc.</option>
                                        <option value="0.5">50% desc.</option>
                                        <option value="1">100% desc.</option>
                                    </select>
                                </div>
                            </div>

                            <div class="sim-checkbox-row" style="background-color: #eef9f1; border-color: #c3e6cb; flex-direction: column; align-items: flex-start;">
                                <div class="header-toggle" onclick="toggleVisibility('seccionKavakInput', 'arrowKavak')">
                                    <span class="sim-label" style="margin-bottom:0;">Opciones Kavak Total</span>
                                    <div class="toggle-arrow" id="arrowKavak">▼</div>
                                </div>
                                <div id="seccionKavakInput" style="display: block; width: 100%;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                                        <div class="sim-checkbox-row" style="margin:0; padding: 10px; background-color: white;">
                                            <input type="checkbox" id="checkKavakLite" onchange="handleKavakCheck('lite')">
                                            <label for="checkKavakLite">Kavak Total Lite</label>
                                        </div>
                                        <div class="sim-checkbox-row" style="margin:0; padding: 10px; background-color: white;">
                                            <input type="checkbox" id="checkKavakPremium" onchange="handleKavakCheck('premium')">
                                            <label for="checkKavakPremium">Kavak Total Premium</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="sim-checkbox-row" style="background-color: #fff9db; border-color: #ffe066;">
                                <input type="checkbox" id="checkReserva">
                                <label for="checkReserva"><strong>Reserva / Previa Hecha (-$100.000)</strong></label>
                            </div>

                            <div class="sim-btn-container">
                                <button class="btn-clear" onclick="limpiarSimulador()">Limpiar Todo</button>
                                <button class="btn-pdf-sim" onclick="generarPDFSimulador()">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Generar PDF
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="sim-col-half">
                        <div class="sim-card">
                            <h2 class="sim-h2">Cuadro Contable Detallado</h2>
                            <div class="sim-results" id="simResultContainer">
                                
                                <div class="sim-valor-auto">
                                    <div class="sim-subtitle">VALOR AUTO</div>
                                    <div class="sim-row" style="margin-bottom: 8px; border-bottom: 1px dashed #ccc; padding-bottom: 5px;">
                                        <span style="color: #666;">Stock ID:</span>
                                        <span class="sim-val" id="resStockId" style="color: #000;">-</span>
                                    </div>
                                    <div class="sim-row" id="rowPrecioContado">
                                        <span>Precio Contado:</span>
                                        <span class="sim-val" id="resPrecioContado">$ 0</span>
                                    </div>
                                </div>

                                <div class="sim-divider"></div>
                                
                                <div class="sim-subtitle">BONOS</div>
                                <div class="sim-row" id="rowBonoWeb">
                                    <span>Bono promoción web:</span>
                                    <span id="resBono">$ 0</span>
                                </div>
                                <div id="detalleBonosContable" style="display:none;">
                                    <div id="itemsBonos"></div>
                                </div>
                                <div class="sim-row" id="rowTotalBonos" style="margin-top: 8px;">
                                    <strong style="color: #555;">Total Bonificaciones:</strong>
                                    <span class="sim-val" style="color: #555; font-weight: 700;" id="resTotalBonos">$ 0</span>
                                </div>
                                <div class="sim-total-auto">
                                    <strong class="c-red">Valor Auto c/dsctos:</strong>
                                    <span class="sim-val c-red" id="resTotalAutoDsctos">$ 0</span>
                                </div>

                                <div class="sim-divider"></div>
                                
                                <div class="sim-subtitle">ADICIONALES Y DOCUMENTALES</div>
                                <div id="seccionServiciosRes">
                                    <div class="sim-sub-item">
                                        <span>Impuesto Transf. (1.5%):</span>
                                        <span id="resImpuesto">$ 0</span>
                                    </div>
                                    <div class="sim-sub-item">
                                        <span>Gastos Admin (3.5%):</span>
                                        <span id="resGtosAdmin">$ 0</span>
                                    </div>
                                    <div id="detalleDescGtosAdmin" class="sim-sub-detail oculto">
                                        <span id="labelDescGtosAdmin">Descuento aplicado:</span>
                                        <span id="resDescGtosAdmin">- $ 0</span>
                                    </div>
                                    <div class="sim-sub-item">
                                        <span id="labelKavak">Kavak Total:</span>
                                        <span id="resKavak">$ 0</span>
                                    </div>
                                    <div class="sim-row sim-sub-gastos">
                                        <strong class="c-green">Total Gastos Operacionales:</strong>
                                        <span class="sim-val c-green" id="resTotalServicios">$ 0</span>
                                    </div>
                                </div>

                                <div class="sim-divider"></div>
                                
                                <div class="sim-row" style="margin-top: 15px; margin-bottom: 5px;">
                                    <strong class="c-blue">TOTAL A PAGAR (Auto + Gastos):</strong>
                                    <span class="sim-val c-blue" id="resSubtotalPagar">$ 0</span>
                                </div>

                                <div class="sim-divider"></div>

                                <div class="sim-summary-box">
                                    <div style="font-weight: 800; color: #333; margin-bottom: 8px; text-transform: uppercase; margin-top: 5px; font-size: 13px;">
                                        INFORMACIÓN PARA SALESFORCE:
                                    </div>
                                    <div class="sim-row">
                                        <span>Monto Simulado (Precio Vehículo):</span>
                                        <span class="sim-val" id="resMontoSimulado">$ 0</span>
                                    </div>
                                    <div class="sim-row">
                                        <span id="labelAdelantoSimulado">Adelanto Simulado (Pie):</span>
                                        <span class="sim-val" id="resAdelantoSimulado">$ 0</span>
                                    </div>
                                    <div class="sim-row oculto" id="rowTradeInSalesforce" style="color: #d35400;">
                                        <span>Retoma / Trade In:</span>
                                        <span class="sim-val" id="resTradeInSalesforce">$ 0</span>
                                    </div>
                                    <div class="sim-row">
                                        <span>Monto más intereses (Crédito):</span>
                                        <span class="sim-val" id="resMontoIntereses">$ 0</span>
                                    </div>
                                    
                                    <div class="sim-divider" style="background-color: #ccc;"></div>
                                    
                                    <div id="itemReservaContable" class="oculto">
                                        <div class="sim-row" style="color: #f08c00; font-weight: bold; margin-bottom: 10px;">
                                            <span>Reserva / R. Previa:</span>
                                            <span>- $ 100.000</span>
                                        </div>
                                    </div>
                                    
                                    <div class="sim-row" style="margin-top: 10px;">
                                        <strong style="color: #333;" id="labelSaldoFinal">Saldo pendiente a abonar:</strong>
                                        <span class="sim-val c-purple" id="resTotalCaja">$ 0</span>
                                    </div>
                                    
                                    <div id="rowSaldoFavor" class="oculto" style="margin-top: 10px; padding: 5px; background: #e8f5e9; border: 1px dashed green; border-radius: 4px;">
                                        <div class="sim-row" style="margin:0;">
                                            <strong style="color: #198754;">Saldo Trade In:</strong>
                                            <span class="sim-val c-green" id="resSaldoFavor">$ 0</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div id="mensajeError" style="color: #dc3545; text-align: center; margin-top: 15px; display: none; font-weight: bold;"></div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- SCRIPT DEL SIMULADOR -->
    <script>
        document.getElementById('openSidebar').addEventListener('click', () => { document.getElementById('sidebar').classList.add('active'); document.getElementById('sidebarOverlay').classList.add('active'); });
        document.getElementById('sidebarOverlay').addEventListener('click', () => { document.getElementById('sidebar').classList.remove('active'); document.getElementById('sidebarOverlay').classList.remove('active'); });

        var memoriaPorcentajeUsuario = "35"; 
        var memoriaMontoBilletera = 0; 

        const formatCLP = (num) => new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 }).format(num);
        const cleanNum = (str) => parseFloat(str ? str.toString().replace(/\D/g, '') : '0') || 0;

        function initAll() {
            document.getElementById('precioContado').value = formatCLP(11795900);
            document.getElementById('precioCredito').value = formatCLP(10895900);
            document.getElementById('porcentajePie').value = "35";
            
            toggleVisibility('seccionBonosInputs', 'arrowBonos', false); 
            toggleVisibility('seccionKavakInput', 'arrowKavak', true);
            document.getElementById('checkKavakLite').checked = true;
            
            setupSimuladorListeners();
            calcular('porcentaje');
        }

        function setupSimuladorListeners() {
            ['precioContado', 'precioCredito', 'montoPie', 'montoTradeIn', 'montoAbonoAdicional'].forEach(id => {
                const el = document.getElementById(id);
                if(el){
                    el.addEventListener('input', (e) => {
                        const val = cleanNum(e.target.value);
                        
                        if (id === 'montoPie') {
                             if(document.getElementById('checkPieIncluyeGastos').checked) {
                                 e.target.value = val ? formatCLP(val) : "";
                             } else {
                                 memoriaMontoBilletera = val;
                                 e.target.value = val ? formatCLP(val) : "";
                             }
                        } else {
                            e.target.value = val ? formatCLP(val) : ""; 
                        }

                        let org = (id === 'montoPie') ? 'monto' : 'base';
                        calcular(org);
                    });
                }
            });

            const elPorc = document.getElementById('porcentajePie');
            if(elPorc) elPorc.addEventListener('input', () => calcular('porcentaje'));

            ['checkExcepcion', 'checkReserva', 'bonoReservaPrev', 'bonoCRM', 'bonoDetailing'].forEach(id => {
                const el = document.getElementById(id);
                if(el) el.addEventListener('change', () => calcular('base'));
            });

            ['bonoComercial', 'bonoDiscrecional', 'bonoGtosAdmin'].forEach(id => {
                const el = document.getElementById(id);
                if(el) el.addEventListener('change', () => calcular('porcentaje'));
            });

            const radiosTrade = document.querySelectorAll('input[name="tradeInOption"]');
            radiosTrade.forEach(radio => {
                radio.addEventListener('change', () => calcular('monto'));
            });
        }

        function handleEnter(e) { if(e.key === 'Enter') buscarStock(); }
        
        function buscarStock() {
            const id = document.getElementById('txtStockId').value;
            const msgDiv = document.getElementById('searchMsg');
            const btn = document.getElementById('btnBuscar');
            const spinner = document.getElementById('searchSpinner');
            
            if (!id) { msgDiv.innerText = "Ingrese ID."; msgDiv.style.color = "red"; return; }
            
            btn.disabled = true; spinner.style.display = "inline-block"; 
            msgDiv.innerText = "Consultando Base de Datos..."; msgDiv.style.color = "#666";
            
            fetch('index.php?action=ajax_cotizador_search', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'stock_id=' + encodeURIComponent(id)
            })
            .then(r => r.json())
            .then(response => {
                btn.disabled = false; spinner.style.display = "none";
                if (response.success) {
                    msgDiv.innerText = "Datos encontrados."; msgDiv.style.color = "green";
                    document.getElementById('resStockId').innerText = id;
                    document.getElementById('precioContado').value = formatCLP(response.precioContado);
                    document.getElementById('precioCredito').value = formatCLP(response.precioCredito);
                    calcular('base');
                } else { 
                    msgDiv.innerText = response.message || "Ingrese los precios manualmente (Sin Integración externa)."; msgDiv.style.color = "red"; 
                }
            })
            .catch(error => {
                btn.disabled = false; spinner.style.display = "none";
                msgDiv.innerText = "Puedes seguir usando el cotizador sin conexión externa introduciendo montos a mano."; msgDiv.style.color = "red";
            });
        }

        function limpiarSimulador() {
            document.getElementById('txtStockId').value = ""; document.getElementById('searchMsg').innerText = "";
            document.getElementById('resStockId').innerText = "-";
            document.getElementById('precioContado').value = ""; document.getElementById('precioCredito').value = "";
            document.getElementById('porcentajePie').value = ""; document.getElementById('montoPie').value = "";
            document.getElementById('montoTradeIn').value = ""; document.getElementById('montoAbonoAdicional').value = "";
            
            document.getElementById('checkEnableTradeIn').checked = false;
            document.getElementById('checkAbonoAdicional').checked = false;
            document.getElementById('divAbonoAdicional').style.display = 'none';
            document.getElementById('tiOpt1').checked = true;
            document.getElementById('checkPieIncluyeGastos').checked = false; 
            document.getElementById('checkExcepcion').checked = false;
            document.getElementById('checkReserva').checked = false;
            
            document.getElementById('bonoComercial').value = "0"; 
            document.getElementById('bonoDiscrecional').value = "0";
            document.getElementById('bonoGtosAdmin').value = "0";
            
            document.getElementById('bonoReservaPrev').checked = false; 
            document.getElementById('bonoCRM').checked = false;
            document.getElementById('bonoDetailing').checked = false;
            
            toggleVisibility('seccionBonosInputs', 'arrowBonos', false);
            toggleVisibility('seccionTradeIn', 'arrowTradeIn', false);
            
            document.getElementById('checkKavakLite').checked = true; 
            document.getElementById('checkKavakPremium').checked = false;
            toggleVisibility('seccionKavakInput', 'arrowKavak', true);
            
            memoriaPorcentajeUsuario = "35";
            memoriaMontoBilletera = 0; 
            
            calcular('base');
        }

        function toggleVisibility(contentId, arrowId, forceState = null) {
            const content = document.getElementById(contentId);
            const arrow = document.getElementById(arrowId);
            let isVisible = (forceState !== null) ? forceState : (content.style.display === 'block');
            
            if (forceState === null) { isVisible = !isVisible; }
            
            if (isVisible) { 
                content.style.display = 'block'; 
                if(arrow) arrow.classList.add('rotate-up'); 
            } else { 
                content.style.display = 'none'; 
                if(arrow) arrow.classList.remove('rotate-up'); 
            }
        }

        function toggleTradeInState() {
             const isChecked = document.getElementById('checkEnableTradeIn').checked;
             toggleVisibility('seccionTradeIn', 'arrowTradeIn', isChecked);
             
             if (!isChecked && !document.getElementById('checkExcepcion').checked) {
                 document.getElementById('porcentajePie').disabled = false;
                 document.getElementById('montoPie').disabled = false;
                 document.getElementById('checkPieIncluyeGastos').disabled = false;
             }
             calcular('monto');
        }

        function toggleTradeInStateFromHeader() {
            const isVisible = document.getElementById('seccionTradeIn').style.display === 'block';
            toggleVisibility('seccionTradeIn', 'arrowTradeIn', !isVisible);
            document.getElementById('checkEnableTradeIn').checked = !isVisible;
            toggleTradeInState();
        }

        function toggleAbonoInput() {
            const isChecked = document.getElementById('checkAbonoAdicional').checked;
            const div = document.getElementById('divAbonoAdicional');
            if (isChecked) { div.style.display = 'block'; } else { div.style.display = 'none'; document.getElementById('montoAbonoAdicional').value = ''; }
            calcular('monto');
        }
        
        function toggleIncluyeGastos() { 
            const chk = document.getElementById('checkPieIncluyeGastos');
            const inp = document.getElementById('montoPie');

            if (!chk.checked) {
                if(memoriaMontoBilletera > 0) {
                     inp.value = formatCLP(memoriaMontoBilletera);
                }
            } else {
                const valActual = cleanNum(inp.value);
                if(valActual > 0) memoriaMontoBilletera = valActual;
            }

            calcular('monto'); 
        }
        
        function toggleExcepcion() {
            const chk = document.getElementById('checkExcepcion');
            const inpPorc = document.getElementById('porcentajePie');
            const inpMonto = document.getElementById('montoPie');
            
            if (chk.checked) {
                inpPorc.value = "40.0"; inpPorc.disabled = true; inpMonto.disabled = true;
            } else {
                const useTradeIn = document.getElementById('checkEnableTradeIn').checked;
                const tradeInMode = document.querySelector('input[name="tradeInOption"]:checked').value;
                inpPorc.value = memoriaPorcentajeUsuario;
                
                if (!useTradeIn) { 
                    inpPorc.disabled = false; inpMonto.disabled = false; 
                } else {
                    if(tradeInMode !== '1' && tradeInMode !== '2' && tradeInMode !== '3') { 
                        inpPorc.disabled = false; inpMonto.disabled = false; 
                    }
                }
            }
            calcular('porcentaje');
        }

        function handleKavakCheck(selected) {
            const lite = document.getElementById('checkKavakLite');
            const premium = document.getElementById('checkKavakPremium');
            if (selected === 'lite' && lite.checked) { premium.checked = false; } 
            else if (selected === 'premium' && premium.checked) { lite.checked = false; }
            calcular();
        }

        function calcular(origen) {
            const vContadoOriginal = cleanNum(document.getElementById('precioContado').value);
            const vCredito = cleanNum(document.getElementById('precioCredito').value);
            const esExcepcion = document.getElementById('checkExcepcion').checked;
            const resCheck = document.getElementById('checkReserva').checked;
            const inpPorc = document.getElementById('porcentajePie');
            const inpMonto = document.getElementById('montoPie');
            
            if (!esExcepcion && !inpPorc.disabled && document.activeElement === inpPorc) { 
                memoriaPorcentajeUsuario = inpPorc.value; 
            }

            let bonosManuales = 0; let htmlBonos = "";
            const bc = parseFloat(document.getElementById('bonoComercial').value);
            if(bc > 0) { bonosManuales += bc; htmlBonos += `<div class="sim-sub-item"><span>Bono Comercial:</span><span>- ${formatCLP(bc)}</span></div>`; }
            if(document.getElementById('bonoReservaPrev').checked) { bonosManuales += 50000; htmlBonos += `<div class="sim-sub-item"><span>Bono Reserva:</span><span>- $ 50.000</span></div>`; }
            if(document.getElementById('bonoCRM').checked) { bonosManuales += 100000; htmlBonos += `<div class="sim-sub-item"><span>Bono CRM:</span><span>- $ 100.000</span></div>`; }
            if(document.getElementById('bonoDetailing').checked) { bonosManuales += 50000; htmlBonos += `<div class="sim-sub-item"><span>Bono Detailing:</span><span>- $ 50.000</span></div>`; }
            
            const bd = parseFloat(document.getElementById('bonoDiscrecional').value);
            if(bd > 0) { bonosManuales += bd; htmlBonos += `<div class="sim-sub-item"><span>Bono Discrecional:</span><span>- ${formatCLP(bd)}</span></div>`; }
            
            const divDetalleBonos = document.getElementById('detalleBonosContable');
            const containerBonos = document.getElementById('itemsBonos');
            if(bonosManuales > 0) { 
                divDetalleBonos.style.display = 'block'; containerBonos.innerHTML = htmlBonos; 
            } else { 
                divDetalleBonos.style.display = 'none'; 
            }

            const useTradeIn = document.getElementById('checkEnableTradeIn').checked;
            const useAbono = document.getElementById('checkAbonoAdicional').checked;
            let vTradeIn = useTradeIn ? cleanNum(document.getElementById('montoTradeIn').value) : 0;
            let vAbono = (useTradeIn && useAbono) ? cleanNum(document.getElementById('montoAbonoAdicional').value) : 0;
            let aporteTotalBruto = vTradeIn + vAbono;
            
            const tradeInMode = document.querySelector('input[name="tradeInOption"]:checked').value;
            const chkIncluye = document.getElementById('checkPieIncluyeGastos');
            
            if (useTradeIn && tradeInMode !== '1') { 
                chkIncluye.disabled = true; chkIncluye.checked = true; 
            } else { 
                if(useTradeIn && tradeInMode === '1') { 
                    chkIncluye.checked = false; chkIncluye.disabled = true; 
                } else if (!esExcepcion) { 
                    chkIncluye.disabled = false; 
                } 
            }
            const incluyeGastos = chkIncluye.checked;

            let porcentajeReferencia = 0; 
            let aplicaBonoWeb = false; 
            let bonoWebMonto = vContadoOriginal - vCredito;
            
            if (esExcepcion) { 
                aplicaBonoWeb = true; porcentajeReferencia = 40; 
            } else if (useTradeIn) { 
                if (tradeInMode === '3') { 
                    porcentajeReferencia = 35; 
                } else { 
                    if(vCredito > 0) porcentajeReferencia = (aporteTotalBruto / vCredito) * 100; 
                } 
            } else {
                if (origen === 'porcentaje' || origen === 'base' || origen === undefined) { 
                    porcentajeReferencia = parseFloat(inpPorc.value.replace(',', '.')) || 0; 
                } else { 
                    let montoIngresado = cleanNum(inpMonto.value); 
                    if(vCredito > 0) porcentajeReferencia = (montoIngresado / vCredito) * 100; 
                }
            }
            if (porcentajeReferencia >= 19.90 && porcentajeReferencia <= 35.10) { aplicaBonoWeb = true; }
            
            let baseParaGastos = aplicaBonoWeb ? (vContadoOriginal - bonoWebMonto) : vContadoOriginal;
            let gastosFinales = calcularGastosInternos(baseParaGastos);
            let valorAutoConDescuentos = baseParaGastos - bonosManuales;

            let totalServicios = gastosFinales.total;
            let montoPieFinal = 0;

            if (esExcepcion) {
                montoPieFinal = Math.round(valorAutoConDescuentos * 0.40);
                if(origen !== 'monto') inpMonto.value = formatCLP(montoPieFinal);
            } 
            else if (useTradeIn) {
                if (tradeInMode === '1') { 
                    montoPieFinal = aporteTotalBruto; 
                    inpMonto.value = formatCLP(montoPieFinal); 
                    inpMonto.disabled = true; inpPorc.disabled = true; 
                    if(valorAutoConDescuentos > 0) inpPorc.value = ((montoPieFinal / valorAutoConDescuentos) * 100).toFixed(1); 
                } else if (tradeInMode === '2') { 
                    montoPieFinal = Math.max(0, aporteTotalBruto - totalServicios + (resCheck ? 100000 : 0)); 
                    inpMonto.value = formatCLP(montoPieFinal); 
                    inpMonto.disabled = true; inpPorc.disabled = true; 
                    if(valorAutoConDescuentos > 0) inpPorc.value = ((montoPieFinal / valorAutoConDescuentos) * 100).toFixed(1); 
                } else if (tradeInMode === '3') { 
                    montoPieFinal = Math.round(valorAutoConDescuentos * 0.35); 
                    inpMonto.value = formatCLP(montoPieFinal); 
                    inpPorc.value = "35.0"; 
                    inpMonto.disabled = true; inpPorc.disabled = true; 
                }
            } 
            else {
                if (!esExcepcion) { inpMonto.disabled = false; inpPorc.disabled = false; }
                
                if (origen === 'porcentaje' || origen === 'base' || origen === undefined) {
                    montoPieFinal = Math.round(valorAutoConDescuentos * (porcentajeReferencia / 100));
                    if (!inpMonto.disabled) inpMonto.value = formatCLP(montoPieFinal);
                    if(!incluyeGastos) memoriaMontoBilletera = montoPieFinal;
                } else {
                    if (incluyeGastos) { 
                        let base = memoriaMontoBilletera;
                        let reserva = resCheck ? 100000 : 0; 
                        montoPieFinal = Math.max(0, base - totalServicios + reserva); 
                        if(document.activeElement !== inpMonto) inpMonto.value = formatCLP(montoPieFinal); 
                    } else { 
                        let inputVal = cleanNum(inpMonto.value);
                        montoPieFinal = inputVal; 
                    }
                    if(valorAutoConDescuentos > 0) inpPorc.value = ((montoPieFinal / valorAutoConDescuentos) * 100).toFixed(1);
                }
            }
            
            if (!esExcepcion && origen !== 'monto' && valorAutoConDescuentos > 0 && !useTradeIn) { 
                if(document.activeElement !== inpPorc) { 
                    inpPorc.value = ((montoPieFinal / valorAutoConDescuentos) * 100).toFixed(1); 
                } 
            }

            let saldoFavor = 0; let totalCaja = 0; let montoCredito = valorAutoConDescuentos - montoPieFinal;
            let costoTotalOperacion = valorAutoConDescuentos + totalServicios;
            if(resCheck) costoTotalOperacion -= 100000;
            
            if (useTradeIn && aporteTotalBruto >= costoTotalOperacion) { 
                montoCredito = 0; montoPieFinal = valorAutoConDescuentos; 
                saldoFavor = aporteTotalBruto - costoTotalOperacion; totalCaja = 0; 
                if(valorAutoConDescuentos > 0) inpPorc.value = "100.0"; 
            } else {
                if (useTradeIn && tradeInMode === '3') { 
                    let costoOpt3 = montoPieFinal + totalServicios; 
                    if(resCheck) costoOpt3 -= 100000; 
                    if (aporteTotalBruto > costoOpt3) { saldoFavor = aporteTotalBruto - costoOpt3; totalCaja = 0; } 
                    else { saldoFavor = 0; totalCaja = costoOpt3 - aporteTotalBruto; } 
                } else {
                    if (useTradeIn && tradeInMode === '1') { 
                        let falta = totalServicios; if(resCheck) falta -= 100000; totalCaja = Math.max(0, falta); 
                    } else if (useTradeIn && tradeInMode === '2') { 
                        totalCaja = 0; 
                    } else { 
                        let reserva = resCheck ? 100000 : 0; 
                        totalCaja = montoPieFinal + totalServicios - reserva; 
                    }
                }
            }

            document.getElementById('resImpuesto').innerText = formatCLP(gastosFinales.imp);
            document.getElementById('resGtosAdmin').innerText = formatCLP(gastosFinales.adm);
            let labelK = "Kavak Total:";
            const isLite = document.getElementById('checkKavakLite').checked; 
            const isPrem = document.getElementById('checkKavakPremium').checked;
            
            if (isLite) labelK = "Kavak Total: Lite (2,34%)"; else if (isPrem) labelK = "Kavak Total: Premium (4,59%)";
            document.getElementById('labelKavak').innerText = labelK;
            document.getElementById('resKavak').innerText = formatCLP(gastosFinales.kavak);
            document.getElementById('resTotalServicios').innerText = formatCLP(totalServicios);

            const filaDesc = document.getElementById('detalleDescGtosAdmin');
            if(gastosFinales.desc > 0) { 
                filaDesc.classList.remove('oculto'); document.getElementById('resDescGtosAdmin').innerText = "- " + formatCLP(gastosFinales.desc); 
            } else { 
                filaDesc.classList.add('oculto'); 
            }
            
            const rowBonoWeb = document.getElementById('rowBonoWeb');
            if(aplicaBonoWeb) { 
                rowBonoWeb.style.display = 'flex'; document.getElementById('resBono').innerText = "- " + formatCLP(bonoWebMonto); 
            } else { 
                rowBonoWeb.style.display = 'none'; 
            }
            
            let totalDescuentos = (aplicaBonoWeb ? bonoWebMonto : 0) + bonosManuales;
            const rowTotalBonos = document.getElementById('rowTotalBonos');
            if(totalDescuentos > 0) { 
                rowTotalBonos.style.display = 'flex'; document.getElementById('resTotalBonos').innerText = "- " + formatCLP(totalDescuentos); 
            } else { 
                rowTotalBonos.style.display = 'none'; 
            }

            document.getElementById('resTotalAutoDsctos').innerText = formatCLP(valorAutoConDescuentos);
            document.getElementById('resSubtotalPagar').innerText = formatCLP(valorAutoConDescuentos + totalServicios);
            document.getElementById('resPrecioContado').innerText = formatCLP(vContadoOriginal);
            document.getElementById('itemReservaContable').classList.toggle('oculto', !resCheck);
            document.getElementById('resMontoSimulado').innerText = formatCLP(valorAutoConDescuentos);
            document.getElementById('labelAdelantoSimulado').innerText = "Adelanto Simulado (Pie " + inpPorc.value + "%):";
            document.getElementById('resAdelantoSimulado').innerText = formatCLP(montoPieFinal);
            document.getElementById('resMontoIntereses').innerText = formatCLP(montoCredito);
            
            if (saldoFavor > 0) {
                document.getElementById('resTotalCaja').innerText = "$ 0"; 
                document.getElementById('rowSaldoFavor').classList.remove('oculto'); 
                document.getElementById('resSaldoFavor').innerText = formatCLP(saldoFavor);
            } else {
                document.getElementById('rowSaldoFavor').classList.add('oculto'); 
                document.getElementById('resTotalCaja').innerText = formatCLP(Math.max(0, totalCaja));
            }
            
            const err = document.getElementById('mensajeError');
            let currentPct = (valorAutoConDescuentos > 0) ? (montoPieFinal / valorAutoConDescuentos) * 100 : 0;
            
            if (vContadoOriginal > 0) {
                if (saldoFavor > 0) { err.style.display = 'none'; } else {
                    if (currentPct < 19.90 && !esExcepcion && montoCredito > 0) { 
                        err.innerText = "¡ALERTA!: El pie no puede ser menor al 20%."; err.style.display = 'block'; 
                    } else if (montoCredito < 1000000 && montoCredito > 0) { 
                        err.innerText = "¡ALERTA!: Saldo a financiar menor al mínimo."; err.style.display = 'block'; 
                    } else { 
                        err.style.display = 'none'; 
                    }
                }
            } else { err.style.display = 'none'; }
        }
        
        function calcularGastosInternos(montoBase) {
                if(montoBase <= 0) return { imp:0, adm:0, kavak:0, desc:0, total:0 };
                let i = Math.floor(montoBase * 0.015);
                let rawAdm = Math.floor(montoBase * 0.035); 
                let admBase = Math.min(Math.max(rawAdm, 250000), 650000);
                
                let factDesc = parseFloat(document.getElementById('bonoGtosAdmin').value);
                let descM = Math.floor(admBase * factDesc); let a = admBase - descM;
                
                let k = 0;
                if (document.getElementById('checkKavakLite').checked) { 
                    let rk = Math.ceil(montoBase * 0.0234); k = Math.min(Math.max(rk, 150000), 300000); 
                } else if (document.getElementById('checkKavakPremium').checked) { 
                    let rk = Math.ceil(montoBase * 0.0459); k = Math.min(Math.max(rk, 350000), 750000); 
                }
                return { imp: i, adm: a, kavak: k, desc: descM, total: i + a + k };
        }

        async function generarPDFSimulador() {
            const { jsPDF } = window.jspdf;
            const element = document.querySelector('.simulador-layout');
            const btnContainer = document.querySelector('.sim-btn-container');
            const searchContainer = document.querySelector('.search-cont');
            
            btnContainer.style.display = 'none';
            searchContainer.style.display = 'none';
            
            try {
                const canvas = await html2canvas(element, { scale: 2 });
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
                
                pdf.text("Simulación de Crédito - Kavak", 10, 10);
                pdf.addImage(imgData, 'PNG', 0, 20, pdfWidth, pdfHeight);
                pdf.save("Simulacion_Kavak.pdf");
            } catch (err) { alert("Error al generar PDF: " + err.message); } 
            finally {
                btnContainer.style.display = 'flex'; searchContainer.style.display = 'block';
            }
        }
    </script>
</body>
</html>
