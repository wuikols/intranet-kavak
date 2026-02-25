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

$topbarTitle = 'Solicitud Pravia 2.0';
$topbarBadge = 'Comercial';
$topbarBadgeClass = 'badge-role';
$topbarBadgeStyle = 'background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2);';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Solicitud Pravia 2.0</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">

    <style>
        /* ESTILOS ESPECÍFICOS PRAVIA */
        :root { --primary-color: #0056b3; --success-color: #28a745; --bg-color-pravia: #f4f4f9; }
        
        .main-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); max-width: 900px; margin: auto; font-family: 'Inter', sans-serif;}
        .step-header { border-left: 6px solid #0d6efd; padding-left: 15px; margin-bottom: 25px; color: #1e293b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;}
        .readonly-field { background-color: #e9ecef; color: #495057; font-weight: 700; cursor: not-allowed; border-color: #ced4da; }
        
        .logo-img { max-width: 200px; width: 100%; height: auto; object-fit: contain; display: block; margin: 0 auto; }
        
        .select2-container--bootstrap-5 .select2-selection { border-color: #ced4da; height: 38px !important; display: flex; align-items: center; }
        .hidden-section { display: none; }
        #sugerenciasComuna .list-group-item { cursor: pointer; }
        #sugerenciasComuna .list-group-item:hover { background-color: #f8f9fa; }
        .fade-in { animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        
        /* VALIDACIONES VISUALES */
        .is-valid-custom { border-color: #198754 !important; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right calc(.375em + .1875rem) center; background-size: calc(.75em + .375rem) calc(.75em + .375rem); }
        .is-invalid-custom { border-color: #dc3545 !important; }
        .select2-hidden-accessible.is-invalid-custom + .select2-container .select2-selection { border-color: #dc3545 !important; }
        .transition-width { transition: width 0.3s ease; }

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
<body>

    <div class="dashboard-layout">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <?php include 'partials/sidebar.php'; ?>

        <main class="main-content">
<?php include 'partials/topbar.php'; ?>

            <section class="content-area" style="padding-top: 20px;">
                <div class="container mb-5">
                    <div class="main-card">
                        <img src="<?php echo BASE_URL; ?>assets/img/LogoPravia.jpeg" alt="Logo Pravia" class="logo-img mb-4">
                        
                        <h2 class="text-center mb-4 fw-bold text-primary">Solicitud de Crédito Automotriz</h2>
                        
                        <div id="step0" class="fade-in">
                            <div class="p-5 bg-light border rounded text-center shadow-sm">
                                <h5 class="text-start mb-3 text-muted border-bottom pb-2">Datos de Origen</h5>
                                <div class="mb-3 text-start">
                                    <label class="form-label fw-bold">Sucursal</label>
                                    <select class="form-select form-select-lg" id="sucursal" onchange="if(this.value) goToStep(1)">
                                        <option value="" selected disabled>-- Seleccione Sucursal --</option>
                                        <option value="Marathon">Marathon</option>
                                        <option value="Mall Barrio Independencia">Mall Barrio Independencia</option>
                                        <option value="Schiappacasse">Schiappacasse</option>
                                        <option value="Conversión">Conversión</option>
                                        <option value="Originación">Originación</option>
                                    </select>
                                </div>
                                <div class="mt-4 pt-3 border-top"><button type="button" class="btn btn-warning w-100 fw-bold shadow-sm text-dark d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#modalFinancieras"><i class="bi bi-bank2 fs-5"></i> VER RESPUESTA DE FINANCIERAS</button></div>
                            </div>
                        </div>

                        <form id="mainForm">
                            <div id="step1" class="hidden-section fade-in">
                                <h4 class="step-header">1. Datos del Cliente</h4>
                                <div class="mb-3"><label class="form-label fw-bold">Nombre completo del Cliente <span class="text-danger">*</span></label><input type="text" class="form-control text-uppercase" id="nombreCliente" required></div>
                                <div class="mb-3"><label class="form-label fw-bold">RUT <span class="text-danger">*</span></label><input type="text" class="form-control" id="rutCliente" placeholder="Ej: 12345678-9" maxlength="12"><div id="rutError" class="text-danger mt-1" style="display:none; font-size: 0.85em;">RUT Inválido.</div></div>
                                <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">Correo Electrónico <span class="text-danger">*</span></label><input type="email" class="form-control" id="emailCliente" placeholder="nombre@ejemplo.cl"><div id="emailError" class="text-danger mt-1" style="display:none; font-size: 0.85em;">Formato inválido (debe ser un correo válido.).</div></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">Teléfono Móvil <span class="text-danger">*</span></label><div class="input-group"><input type="tel" class="form-control" id="telefonoCliente" placeholder="+56 9 1234 5678" maxlength="16"></div><div id="phoneError" class="text-danger mt-1" style="display:none; font-size: 0.85em;">Debe tener 9 dígitos.</div></div></div>
                                <div class="mb-3"><label class="form-label fw-bold">Dirección Particular <span class="text-danger">*</span></label><input type="text" class="form-control" id="direccionCliente" placeholder="Avda. Vitacura #2160, Depto. 104"></div>
                                <div class="mb-3 position-relative"><label class="form-label">Comuna de residencia <span class="text-danger">*</span></label><input type="text" class="form-control" id="comuna" placeholder="Escriba su comuna..." autocomplete="off"><div id="sugerenciasComuna" class="list-group position-absolute w-100 shadow" style="display:none; max-height: 200px; overflow-y: auto; z-index: 1050; top: 100%;"></div><div id="comunaError" class="text-danger mt-1" style="display:none; font-size: 0.85em;">Seleccione una comuna válida.</div></div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Tipo de trabajador <span class="text-danger">*</span></label>
                                        <select class="form-select" id="tipoTrabajador">
                                            <option value="" selected disabled>Seleccione...</option>
                                            <option value="Dependiente">Dependiente</option>
                                            <option value="Independiente">Independiente</option>
                                            <option value="Socio Empresa">Socio Empresa</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3"><label class="form-label fw-bold">Antigüedad laboral <span class="text-danger">*</span></label><input type="text" class="form-control text-uppercase" id="antiguedad" placeholder="EJ: 5 AÑOS"></div>
                                    <div class="col-md-4 mb-3"><label class="form-label fw-bold">Sueldo líquido promedio <span class="text-danger">*</span></label><input type="text" class="form-control money-input" id="sueldoLiquido" placeholder="$ 0"></div>
                                </div>
                                <div id="divDatosEmpleador" class="hidden-section p-3 bg-light border rounded mb-3" style="display:none;">
                                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-building"></i> Datos del Empleador</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">RUT Empleador <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="rutEmpleador" placeholder="Ej: 76.123.456-K" maxlength="12">
                                            <div id="rutEmpleadorError" class="text-danger mt-1" style="display:none; font-size: 0.85em;">RUT Inválido.</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Nombre Empleador (Opcional)</label>
                                            <input type="text" class="form-control text-uppercase" id="nombreEmpleador" placeholder="Nombre Empresa SpA">
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 hidden-section p-3 bg-light border rounded" id="divCarpetaTributaria"><label class="form-label text-success fw-bold">📂 Carpeta Tributaria (PDF Obligatorio)</label><input type="file" class="form-control" id="inputCarpeta" accept="application/pdf"><div class="form-text text-muted">Máximo 5MB. Solo formato PDF.</div></div>
                                <div class="d-flex justify-content-between mt-5 pt-3 border-top"><button type="button" class="btn btn-outline-secondary px-4" onclick="resetFormAndGoBack()">← Cambiar Sucursal</button><button type="button" class="btn btn-primary px-5 fw-bold" id="btnToStep2">Siguiente Paso →</button></div>
                            </div>

                            <div id="step2" class="hidden-section fade-in">
                                <h4 class="step-header">2. Datos del Vehículo</h4>
                                <div class="row"><div class="col-md-6 mb-3"><label class="form-label fw-bold">Año <span class="text-danger">*</span></label><select class="form-select select2-simple" id="anioVehiculo" required></select></div><div class="col-md-6 mb-3"><label class="form-label fw-bold">Marca <span class="text-danger">*</span></label><select class="form-select select2-editable" id="marcaVehiculo" required><option value="">Escriba la marca...</option></select><div class="form-text mt-1 text-muted" style="font-size: 0.85em;"><i class="bi bi-info-circle"></i> Si la marca no aparece, puedes escribirla.</div></div></div>
                                <div class="mb-3 hidden-section" id="divModelo"><label class="form-label fw-bold">Modelo <span class="text-danger">*</span></label><select class="form-select select2-editable" id="modeloVehiculo" required><option value="">Escriba el modelo...</option></select><div class="form-text mt-1 text-muted" style="font-size: 0.85em;"><i class="bi bi-info-circle"></i> Si el modelo no aparece, puedes escribirlo.</div></div>
                                <div class="row"><div class="col-md-6 mb-3 hidden-section" id="divPatente"><label class="form-label fw-bold">Placa Patente (PPU) <span class="text-danger">*</span></label><input type="text" class="form-control text-uppercase fw-bold" id="patente" placeholder="Ej: ABCD12 ó AA1234" maxlength="6"><div id="patenteError" class="text-danger mt-1" style="display:none; font-size: 0.85em;">Patente Inválida (AA1234 o BBBB12).</div></div><div class="col-md-6 mb-3 hidden-section" id="divPrecio"><label class="form-label fw-bold text-primary">Precio de vehículo ($) <span class="text-danger">*</span></label><input type="text" class="form-control money-input fw-bold" id="precioVehiculo" placeholder="$ 0" required></div></div>
                                <div class="d-flex justify-content-between mt-5 pt-3 border-top"><button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(1)">← Volver a Cliente</button><button type="button" class="btn btn-primary px-5 fw-bold" id="btnToStep3">Siguiente Paso →</button></div>
                            </div>

                            <div id="step3" class="hidden-section fade-in">
                                <h4 class="step-header">3. Financiamiento</h4>
                                <div class="row">
                                    <div class="col-12 transition-width mb-3" id="colTipoCredito">
                                        <label class="form-label fw-bold">Tipo de crédito <span class="text-danger">*</span></label>
                                        <select class="form-select" id="tipoCredito">
                                            <option value="" selected>-- Seleccione --</option>
                                            <option value="Crédito Convencional (CC)">Crédito Convencional (CC)</option>
                                            <option value="Crédito Inteligente (CI)">Crédito Inteligente (CI)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3 hidden-section" id="colPlazoCredito">
                                        <label class="form-label fw-bold">Plazo <span class="text-danger">*</span></label>
                                        <select class="form-select" id="plazoCredito"></select>
                                    </div>
                                </div>
                                <div class="row align-items-end">
                                    <div class="col-md-4 mb-3"><label class="form-label fw-bold">% de Pie</label><input type="text" class="form-control" id="inputPorcentajePie" placeholder="0%" oninput="bidirectionalPie('porcentaje')"></div>
                                    <div class="col-md-4 mb-3"><label class="form-label fw-bold">Pie ($) <span class="text-danger">*</span></label><input type="text" class="form-control money-input" id="inputPie" placeholder="$ 0" oninput="bidirectionalPie('monto')"></div>
                                    <div class="col-md-4 mb-3"><label class="form-label text-primary fw-bold">Monto a Financiar</label><input type="text" class="form-control readonly-field" id="montoFinanciar" readonly placeholder="$ 0"></div>
                                </div>
                                <div class="row align-items-end">
                                    <div class="col-md-6"><label class="form-label text-primary fw-bold"><i class="bi bi-link-45deg"></i> Link de Oportunidad Salesforce <span class="text-danger">*</span></label><input type="text" class="form-control" id="salesforceId" placeholder="Ej: https://kavak.lightning.force.com/..."></div>
                                    <div class="col-md-6 mt-3 mt-md-0"><label class="form-label text-danger fw-bold d-none"><i class="bi bi-file-earmark-arrow-up"></i> Glosa Salesforce (PDF)</label><input type="file" class="form-control" id="inputGlosa" accept="application/pdf, image/png, image/jpeg"></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6"><div id="salesforceError" class="text-danger mt-1" style="display:none; font-size: 0.85em;"></div></div>
                                    <div class="col-md-6"><div class="form-text text-muted mt-1" style="font-size: 0.8rem;">Adjunte archivo Cotización/Glosa si corresponde.</div></div>
                                </div>
                                <div class="mb-3"><label class="form-label fw-bold">Comentarios generales</label><textarea class="form-control" id="comentarios" rows="4" placeholder="Ingrese información adicional..."></textarea></div>
                                <div class="d-flex justify-content-between mt-5 pt-3 border-top"><button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(2)">← Volver a Vehículo</button><div class="d-flex justify-content-end gap-2"><button type="button" class="btn btn-success btn-lg px-5 fw-bold shadow" onclick="derivarEvaluacion()">Derivar a Evaluación ✅</button></div></div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="modalConfirmacionEnvio" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title fw-bold">Confirmar Solicitud</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4 text-center">
                                <p class="text-muted mb-4">Por favor revisa que los datos, especialmente el plazo, sean correctos antes de enviar.</p>
                                <div class="bg-light p-3 rounded mb-3 text-start">
                                    <div class="mb-2"><strong>Cliente:</strong> <span id="confCliente"></span></div>
                                    <div class="mb-2"><strong>Vehículo:</strong> <span id="confVehiculo"></span></div>
                                    <div class="mb-2"><strong>Tipo Crédito:</strong> <span id="confTipoCredito"></span></div>
                                </div>
                                <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 0.8rem;">PLAZO SELECCIONADO</h6>
                                <h3 class="text-primary fw-bold display-6 mb-4" id="confPlazo"></h3>
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success btn-lg fw-bold" id="btnConfirmarEnvio" onclick="enviarSolicitudReal()">
                                        <i class="bi bi-send-check me-2"></i> CONFIRMAR Y ENVIAR
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Corregir</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalFinancieras" tabindex="-1" aria-labelledby="lblFinancieras" aria-hidden="true">
                    <div class="modal-dialog modal-fullscreen">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title fw-bold" id="lblFinancieras"><i class="bi bi-bank2 me-2"></i> Respuesta de Financieras</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body p-0" style="background-color: #f8f9fa;">
                                <iframe src="https://script.google.com/macros/s/AKfycbygSPbZzjbaBASglRfRk3Jk7mRqWmXipakL1JC2JAjLUTnRxAPobYldGTravzUldVXgRA/exec" style="width:100%; height:100%; border:none;" title="Respuesta Financieras"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <!-- MAIN APP SCRIPT FOR SIDEBAR -->
    <script src="<?php echo BASE_URL; ?>assets/js/main.js?v=<?php echo time(); ?>"></script>

    <script>
        // --- DATOS ESTÁTICOS NECESARIOS (COMUNAS Y AUTOS) ---
        const comunasChile=["Algarrobo","Alhué","Alto Biobío","Alto del Carmen","Alto Hospicio","Ancud","Andacollo","Angol","Antártica","Antofagasta","Antuco","Arauco","Arica","Aysén","Buin","Bulnes","Cabildo","Cabo de Hornos","Cabrero","Calama","Calbuco","Caldera","Calera","Calera de Tango","Calle Larga","Camarones","Camiña","Canela","Cañete","Carahue","Cartagena","Casablanca","Castro","Catemu","Cauquenes","Cerrillos","Cerro Navia","Chaitén","Chañaral","Chépica","Chiguayante","Chile Chico","Chillán","Chillán Viejo","Chimbarongo","Cholchol","Chonchi","Cisnes","Cobquecura","Cochamó","Cochrane","Codegua","Coelemu","Coihueco","Coinco","Colbún","Colchane","Colina","Collipulli","Coltauco","Combarbalá","Concepción","Conchalí","Concón","Constitución","Contulmo","Copiapó","Coquimbo","Coronel","Corral","Coyhaique","Cunco","Curacautín","Curacaví","Curaco de Vélez","Curanilahue","Curarrehue","Curepto","Curicó","Dalcahue","Diego de Almagro","Doñihue","El Bosque","El Carmen","El Monte","El Quisco","El Tabo","Empedrado","Ercilla","Estación Central","Florida","Freire","Freirina","Fresia","Frutillar","Futaleufú","Futrono","Galvarino","General Lagos","Gorbea","Graneros","Guaitecas","Hijuelas","Hualaihué","Hualañé","Hualpén","Hualqui","Huara","Huasco","Huechuraba","Illapel","Independencia","Iquique","Isla de Maipo","Isla de Pascua","Juan Fernández","La Calera","La Cisterna","La Cruz","La Estrella","La Florida","La Granja","La Higuera","La Ligua","La Pintana","La Reina","La Serena","La Unión","Lago Ranco","Lago Verde","Laguna Blanca","Laja","Lampa","Lanco","Las Cabras","Las Condes","Lautaro","Lebu","Licantén","Limache","Linares","Litueche","Llanquihue","Lo Barnechea","Lo Espejo","Lo Prado","Lolol","Loncoche","Longaví","Lonquimay","Los Álamos","Los Andes","Los Ángeles","Los Lagos","Los Muermos","Los Sauces","Los Vilos","Lumaco","Machalí","Macul","Máfil","Maipú","Malloa","Marchigüe","María Elena","María Pinto","Mariquina","Maule","Maullín","Mejillones","Melipeuco","Melipilla","Molina","Monte Patria","Mostazal","Mulchén","Nacimiento","Nancagua","Natales","Navidad","Negrete","Ninhue","Ñiquén","Nogales","Nueva Imperial","Ñuñoa","O'Higgins","Olivar","Ollagüe","Olmué","Osorno","Ovalle","Padre Hurtado","Padre Las Casas","Paihuano","Paillaco","Paine","Palena","Palmilla","Panguipulli","Panquehue","Papudo","Paredones","Parral","Pedro Aguirre Cerda","Pelarco","Pelluhue","Pemuco","Peñaflor","Peñalolén","Pencahue","Penco","Peralillo","Perquenco","Petorca","Peumo","Pica","Pichidegua","Pichilemu","Pinto","Pirque","Pitrufquén","Placilla","Portezuelo","Porvenir","Pozo Almonte","Primavera","Providencia","Puchuncaví","Pucón","Pudahuel","Puente Alto","Puerto Montt","Puerto Octay","Puerto Varas","Pumanque","Punitaqui","Punta Arenas","Puqueldón","Purén","Purranque","Putaendo","Putre","Puyehue","Queilén","Quellón","Quemchi","Quilaco","Quilicura","Quilleco","Quillón","Quillota","Quilpué","Quinchao","Quinta de Tilcoco","Quinta Normal","Quintero","Quirihue","Rancagua","Ránquil","Rauco","Recoleta","Renaico","Renca","Rengo","Requínoa","Retiro","Rinconada","Río Bueno","Río Claro","Río Hurtado","Río Ibáñez","Río Negro","Río Verde","Romeral","Saavedra","Sagrada Familia","Salamanca","San Antonio","San Bernardo","San Carlos","San Clemente","San Esteban","San Fabián","San Felipe","San Fernando","San Gregorio","San Ignacio","San Javier","San Joaquín","San José de Maipo","San Juan de la Costa","San Miguel","San Nicolás","San Pablo","San Pedro","San Pedro de Atacama","San Pedro de la Paz","San Rafael","San Ramón","San Rosendo","San Vicente","Santa Bárbara","Santa Cruz","Santa Juana","Santa María","Santiago","Santo Domingo","Sierra Gorda","Talagante","Talca","Talcahuano","Taltal","Temuco","Teno","Teodoro Schmidt","Tierra Amarilla","Til Til","Timaukel","Tirúa","Tocopilla","Toltén","Tomé","Torres del Paine","Tortel","Traiguén","Trehuaco","Tucapel","Valdivia","Vallenar","Valparaíso","Vichuquén","Victoria","Vicuña","Vilcún","Villa Alegre","Villa Alemana","Villarrica","Viña del Mar","Vitacura","Yerbas Buenas","Yumbel","Yungay","Zapallar"];
        const carData={"Chevrolet":["Sail","Onix","Prisma","Spark","Cruze","Tracker","Groove","Captiva","Equinox","Traverse","Tahoe","Suburban","Silverado","Colorado","D-Max","N400","Camaro","Corvette","Bolt","Montana"],"Toyota":["Yaris","Corolla","Corolla Cross","Prius","C-HR","RAV4","Rush","Fortuner","4Runner","Land Cruiser","Hilux","Tundra","Hiace","Raize","Agya"],"Nissan":["Versa","Sentra","V-Drive","March","Kicks","Qashqai","X-Trail","Pathfinder","Murano","Navara","NP300","Terrano"],"Hyundai":["Accent","Grand i10","i20","Elantra","Sonata","Venue","Creta","Tucson","Santa Fe","Palisade","Staria","Porter","H1","Kona"],"Kia":["Morning","Soluto","Rio 4","Rio 5","Cerato","Sonet","Seltos","Sportage","Sorento","Carnival","Frontier","Carens"],"Suzuki":["Alto","S-Presso","Celerio","Swift","Dzire","Baleno","Ciaz","Jimny","Ignis","Vitara","Grand Vitara","S-Cross","Ertiga","XL7","Fronx"],"Peugeot":["208","301","308","2008","3008","5008","Partner","Rifter","Expert","Boxer","Landtrek"],"Ford":["EcoSport","Territory","Escape","Edge","Explorer","Ranger","F-150","Maverick","Mustang","Transit","Bronco"],"Mazda":["Mazda 2","Mazda 3","Mazda 6","CX-3","CX-30","CX-5","CX-50","CX-60","CX-9","MX-5","BT-50"],"Mitsubishi":["Mirage","Eclipse Cross","ASX","Outlander","Montero Sport","L200","Katana","Xpander"],"MG":["MG 3","MG 5","MG 6","MG GT","MG ZS","MG ZX","MG HS","MG RX5","MG One","Marvel R","MG 4"],"Chery":["Tiggo 2","Tiggo 2 Pro","Tiggo 3","Tiggo 7 Pro","Tiggo 8","Tiggo 8 Pro","Arrizo 8","Grand Tiggo"],"Changan":["Alsvin","CS15","CS35 Plus","CS55 Plus","UNI-T","UNI-K","Hunter","X7 Plus","CX70"],"JAC":["JS2","JS3","JS4","JS6","JS8","T6","T8","T8 Pro","Refine","Sunray"],"Great Wall":["Poer","Wingle 5","Wingle 7","Cannon","M4"],"Haval":["Jolion","H6","H6 GT","Dargo"],"Maxus":["T60","T90","D60","D90","G10","V80","V90","Deliver 9"],"Volkswagen":["Polo","Virtus","Voyage","Gol","Golf","Jetta","T-Cross","Nivus","Taos","Tiguan","Atlas","Amarok","Saveiro","Transporter"],"Subaru":["Impreza","XV","Crosstrek","Forester","Outback","Evoltis","WRX","Legacy"],"Honda":["City","Civic","Accord","WR-V","HR-V","ZR-V","CR-V","Pilot","Ridgeline"],"Jeep":["Renegade","Compass","Cherokee","Grand Cherokee","Wrangler","Gladiator","Commander"],"RAM":["700","1000","1500","2500","Rampage","V700"],"Citroen":["C3","C3 Aircross","C4","C5 Aircross","C-Elysée","Berlingo","Jumpy","Jumper"],"Renault":["Kwid","Sandero","Logan","Stepway","Duster","Arkana","Koleos","Oroch","Alaskan","Master","Symbol","Captur"],"BMW":["Serie 1","Serie 2","Serie 3","Serie 4","Serie 5","Serie 7","X1","X2","X3","X4","X5","X6","X7"],"Mercedes-Benz":["Clase A","Clase C","Clase E","Clase S","CLA","GLA","GLB","GLC","GLE","GLS","Sprinter","Vito"],"Audi":["A1","A3","A4","A5","A6","Q2","Q3","Q5","Q7","Q8"],"Volvo":["XC40","XC60","XC90","C40","S60","V60"],"Jetour":["X70","X70 Plus","Dashing"],"Geely":["Coolray","Azkarra","Okavango","Geometry C"],"BYD":["Song Plus","Yuan Plus","Dolphin","Seal","Han","Tang"],"Omoda":["C5"],"Jaecoo":["7"],"GAC":["GS3","GS4","Emzoom","Emkoo"],"SsangYong (KGM)":["Tivoli","Korando","Rexton","Musso","Musso Grand","Torres"],"Fiat":["Mobi","Argo","Cronos","Pulse","Fastback","500","Ducato","Fiorino","Strada"]};

        $(document).ready(function() {
            // Inicializar Select2
            $('.select2-simple').select2({ theme: 'bootstrap-5', minimumResultsForSearch: Infinity, width: '100%' });
            $('.select2-editable').select2({ theme: 'bootstrap-5', width: '100%', tags: true, placeholder: "Escriba para buscar..." });

            // --- LISTENERS TRABAJADOR ---
            $('#tipoTrabajador').on('change', function() {
                const val = $(this).val();
                if(val === 'Independiente' || val === 'Socio Empresa') { $('#divCarpetaTributaria').slideDown(); } 
                else { $('#divCarpetaTributaria').slideUp(); $('#inputCarpeta').val(''); }
                if (val === 'Dependiente') { $('#divDatosEmpleador').slideDown(); } 
                else { $('#divDatosEmpleador').slideUp(); $('#rutEmpleador').val(''); $('#nombreEmpleador').val(''); }
            });

            // --- AUTO FORMATOS LIVE (RUT, TELEFONO) ---
            $('#rutCliente, #rutEmpleador').on('input', function() { formatearRutLive(this); });
            $('#telefonoCliente').on('input', function() { formatearTelefonoLive(this); });
            
            // --- FORMATO DIRECCIÓN (Blur) ---
            $('#direccionCliente').on('blur', function() { formatearDireccion(this); });

            // --- VALIDACIÓN DE PATENTE ---
            $('#patente').on('blur', function() { validarPatenteChilena(this); });

            // --- VALIDACIONES DE RUT EN BLUR ---
            $('#rutCliente').on('blur', function() { validarRutField(this, '#rutError'); });
            $('#rutEmpleador').on('blur', function() { validarRutField(this, '#rutEmpleadorError'); });

            // --- LÓGICA CRÉDITO Y PLAZO (LAYOUT DINÁMICO) ---
            $('#tipoCredito').on('change', function() {
                const val = $(this).val();
                if(val && val !== "") {
                    $('#colTipoCredito').removeClass('col-12').addClass('col-md-6');
                    $('#colPlazoCredito').removeClass('hidden-section').fadeIn();
                    actualizarPlazos();
                } else {
                    $('#colPlazoCredito').fadeOut(300, function() {
                          $('#colTipoCredito').addClass('col-12').removeClass('col-md-6');
                          $('#plazoCredito').empty(); 
                    });
                }
            });

            // --- LÓGICA PRECIO VEHÍCULO ---
            $('#precioVehiculo').on('input', function() {
                let val = $(this).val().replace(/\D/g, '');
                $(this).val(val ? '$ ' + parseInt(val, 10).toLocaleString('es-CL') : '');
                bidirectionalPie('precio');
            });

            // --- BOTONES DE NAVEGACIÓN Y VALIDACIÓN STEP 1 ---
            $('#btnToStep2').click(function() { 
                let valido=true;
                if(!$('#nombreCliente').val().trim()){ valido=false; $('#nombreCliente').addClass('is-invalid-custom'); }else{ $('#nombreCliente').removeClass('is-invalid-custom'); }
                if(!$('#rutCliente').hasClass('is-valid-custom')){ valido=false; $('#rutCliente').addClass('is-invalid-custom'); }
                
                const email = $('#emailCliente').val(); 
                const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if(!regexEmail.test(email)) { valido=false; $('#emailCliente').addClass('is-invalid-custom'); $('#emailError').show(); } else { $('#emailCliente').removeClass('is-invalid-custom'); $('#emailError').hide(); }
                
                // Validar teléfono largo (11 dígitos tras formateo)
                const fonoClean = $('#telefonoCliente').val().replace(/\D/g, ''); 
                if(fonoClean.length < 11) { valido=false; $('#telefonoCliente').addClass('is-invalid-custom'); $('#phoneError').show(); } else { $('#telefonoCliente').removeClass('is-invalid-custom'); $('#phoneError').hide(); }
                
                if(valido) goToStep(2); 
            });

            // --- VALIDACIÓN STEP 2 ---
            $('#btnToStep3').click(function() {
                 let valido=true;
                 if(!$('#precioVehiculo').val()){ valido=false; $('#precioVehiculo').addClass('is-invalid-custom'); }
                 // Validación extra de Patente si está visible
                 if(!$('#patente').hasClass('is-valid-custom') && $('#divPatente').is(':visible')) { valido = false; $('#patente').addClass('is-invalid-custom'); $('#patenteError').show(); }
                 
                 if(valido) goToStep(3);
            });
            
            // --- APLICAR FORMATO MONEDA ---
            $('.money-input').not('#precioVehiculo').not('#inputPie').on('keyup',function(){ let val=$(this).val().replace(/\D/g,''); if(val) $(this).val('$ '+parseInt(val,10).toLocaleString('es-CL')); else $(this).val(''); });
            
            // --- AUTOCOMPLETADO COMUNA ---
            const inputComuna = $('#comuna'); const listaComuna = $('#sugerenciasComuna');
            inputComuna.on('focus input', function() { const valor = $(this).val().toLowerCase(); listaComuna.empty(); const filtradas = comunasChile.filter(c => c.toLowerCase().includes(valor)); if (filtradas.length > 0) { filtradas.forEach(c => { listaComuna.append(`<a class="list-group-item list-group-item-action" onclick="seleccionarComuna('${c}')">${c}</a>`); }); listaComuna.show(); } else { listaComuna.hide(); } });
            window.seleccionarComuna = function(nombre) { $('#comuna').val(nombre); $('#sugerenciasComuna').hide(); $('#comuna').addClass('is-valid-custom').removeClass('is-invalid-custom'); };

            // --- CARGAR AÑOS Y MARCAS ---
            const currentYear=new Date().getFullYear(); let yearOptions='<option value="">Seleccione Año</option>'; for(let i=currentYear+1;i>=2010;i--) yearOptions+=`<option value="${i}">${i}</option>`; $('#anioVehiculo').html(yearOptions); 
            let brandOptions='<option value="">Seleccione o Escriba Marca</option>'; Object.keys(carData).sort().forEach(brand=>brandOptions+=`<option value="${brand}">${brand}</option>`); $('#marcaVehiculo').append(brandOptions);
            
            // --- CARGA DINÁMICA DE MODELOS ---
            $('#marcaVehiculo').on('change',function(){ const selectedBrand=$(this).val(); const models=carData[selectedBrand]; $('#modeloVehiculo').empty().append('<option value="">Escriba el modelo...</option>'); if(models) models.sort().forEach(model=>$('#modeloVehiculo').append(new Option(model,model,false,false))); $('#divModelo').show(); });
            $('#modeloVehiculo').on('change', function() { if($(this).val()) { $('#divPatente').removeClass('hidden-section').show(); $('#divPrecio').removeClass('hidden-section').show(); } });
        });

        // --- FUNCIONES DE FORMATO Y VALIDACIÓN ---
        function formatearRutLive(input) {
            let actual = input.value.replace(/^0+/, "");
            let rutClean = actual.replace(/[^0-9kK]/g, "");
            if (rutClean != '') {
                let cuerpo = rutClean.slice(0, -1);
                let dv = rutClean.slice(-1).toUpperCase();
                input.value = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".") + (cuerpo.length > 0 ? "-" : "") + dv;
            } else {
                input.value = "";
            }
        }

        function validarRutField(input, errorId) {
            let val = input.value.replace(/\./g, '').replace(/-/g, '');
            if(validarRut(input.value)) { 
                $(input).addClass('is-valid-custom').removeClass('is-invalid-custom'); $(errorId).hide(); 
            } else { 
                if(input.value.length > 0) { $(input).addClass('is-invalid-custom').removeClass('is-valid-custom'); $(errorId).show(); }
            }
        }

        function validarRut(rutCompleto) {
            let valor = rutCompleto.replace(/\./g, '').replace(/-/g, '');
            if (!/^[0-9]+[0-9kK]{1}$/.test(valor)) return false;
            let cuerpo = valor.slice(0, -1); let dv = valor.slice(-1).toUpperCase();
            let suma = 0; let multiplo = 2;
            for (let i = 1; i <= cuerpo.length; i++) { let index = multiplo * valor.charAt(cuerpo.length - i); suma = suma + index; if (multiplo < 7) { multiplo = multiplo + 1; } else { multiplo = 2; } }
            let dvEsperado = 11 - (suma % 11); dvEsperado = (dvEsperado == 11) ? 0 : ((dvEsperado == 10) ? 'K' : dvEsperado);
            return (String(dvEsperado) == String(dv));
        }

        function formatearTelefonoLive(input) {
            let numeros = input.value.replace(/\D/g, '');
            if (!numeros.startsWith('569')) {
                if (numeros.length === 0) { input.value = "+56 9 "; return; }
                if (numeros.startsWith('9')) { numeros = '56' + numeros; }
                else { numeros = '569' + numeros; }
            }
            numeros = numeros.substring(0, 11);
            let formateado = "+56 9";
            if (numeros.length > 3) formateado += " " + numeros.substring(3, 7);
            if (numeros.length > 7) formateado += " " + numeros.substring(7, 11);
            input.value = formateado;
        }

        function formatearDireccion(input) {
            let val = input.value;
            if(!val) return;
            val = val.toLowerCase().replace(/(?:^|\s|["'([{])+\S/g, match => match.toUpperCase());
            const palabrasClave = ["Depto", "Casa", "Oficina", "Block", "Local", "Interior", "Int", "Dpto"];
            let palabras = val.split(' ');
            let nuevaDir = [];
            for(let i=0; i < palabras.length; i++) {
                let p = palabras[i];
                if(p.match(/^\d+$/)) {
                    let anterior = (i > 0) ? palabras[i-1].replace(/[.,]/g, '') : "";
                    let esClave = palabrasClave.some(k => anterior.toLowerCase().includes(k.toLowerCase()));
                    if(!esClave && !p.startsWith('#')) { p = "#" + p; }
                }
                nuevaDir.push(p);
            }
            input.value = nuevaDir.join(' ');
        }

        function validarPatenteChilena(input) {
            let val = input.value.toUpperCase().replace(/[^A-Z0-9]/g, ''); 
            input.value = val; 
            const regexAntigua = /^[A-Z]{2}\d{4}$/;
            const regexNueva = /^[A-Z]{4}\d{2}$/;
            if(regexAntigua.test(val) || regexNueva.test(val)) {
                $(input).addClass('is-valid-custom').removeClass('is-invalid-custom');
                $('#patenteError').hide();
            } else {
                if(val.length > 0) {
                    $(input).addClass('is-invalid-custom').removeClass('is-valid-custom');
                    $('#patenteError').show();
                }
            }
        }

        function bidirectionalPie(source) {
            const precioTotal = parseFloat($('#precioVehiculo').val().replace(/\D/g, '')) || 0;
            if(precioTotal === 0) return;
            if (source === 'porcentaje') {
                let pctVal = $('#inputPorcentajePie').val().replace('%', '').replace(',', '.');
                let pct = parseFloat(pctVal);
                if (!isNaN(pct)) {
                    let montoCalc = Math.round(precioTotal * (pct / 100));
                    $('#inputPie').val('$ ' + montoCalc.toLocaleString('es-CL'));
                }
            } else if (source === 'monto') {
                let rawVal = $('#inputPie').val().replace(/\D/g, '');
                let montoVal = parseFloat(rawVal) || 0;
                if(rawVal) $('#inputPie').val('$ ' + parseInt(rawVal, 10).toLocaleString('es-CL')); else $('#inputPie').val('');
                let pctCalc = (montoVal / precioTotal) * 100;
                $('#inputPorcentajePie').val(pctCalc.toFixed(1).replace('.', ',') + '%');
            } else if (source === 'precio') {
                let pctVal = $('#inputPorcentajePie').val().replace('%', '').replace(',', '.');
                let pct = parseFloat(pctVal) || 0;
                let montoCalc = Math.round(precioTotal * (pct / 100));
                $('#inputPie').val('$ ' + montoCalc.toLocaleString('es-CL'));
            }
            const pieReal = parseFloat($('#inputPie').val().replace(/\D/g, '')) || 0;
            const financiar = Math.max(0, precioTotal - pieReal);
            $('#montoFinanciar').val('$ ' + financiar.toLocaleString('es-CL'));
        }

        function actualizarPlazos() {
            const tipo = $('#tipoCredito').val(); const selectPlazo = $('#plazoCredito'); selectPlazo.empty();
            let opciones = []; if (tipo === 'Crédito Convencional (CC)') { opciones = ['12 Meses', '24 Meses', '36 Meses', '48 Meses', '60 Meses']; } else { opciones = ['24 Meses + Cuotón (VFMG)', '36 Meses + Cuotón (VFMG)', '48 Meses + Cuotón (VFMG)']; }
            opciones.forEach(op => { selectPlazo.append(new Option(op, op)); });
        }
        
        function resetFormAndGoBack() {
            $('#mainForm')[0].reset(); 
            $('#sucursal').val('');
            $('#anioVehiculo').val('').trigger('change'); 
            $('#marcaVehiculo').val('').trigger('change');
            $('.hidden-section').hide(); 
            $('#divModelo').hide(); 
            $('#divDatosEmpleador').hide(); 
            $('#divCarpetaTributaria').hide();
            $('.form-control, .form-select').removeClass('is-valid-custom is-invalid-custom');
            $('.text-danger').hide();
            $('#colTipoCredito').addClass('col-12').removeClass('col-md-6');
            $('#colPlazoCredito').hide();
            goToStep(0);
        }

        function goToStep(step) {
            $('#step0, #step1, #step2, #step3').hide();
            $('#step' + step).removeClass('hidden-section').fadeIn();
        }

        // --- MANEJO DE ARCHIVOS Y ENVÍO ---
        function leerArchivo(inputFileId) {
            return new Promise((resolve, reject) => {
                const input = document.getElementById(inputFileId);
                if (input && input.files && input.files.length > 0) {
                    const file = input.files[0];
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const data = e.target.result.split(',')[1];
                        resolve({ nombre: file.name, mimeType: file.type, data: data });
                    };
                    reader.onerror = error => reject(error);
                    reader.readAsDataURL(file);
                } else { resolve(null); }
            });
        }

        function derivarEvaluacion() {
            const sfId = $('#salesforceId').val();
            if(!sfId) { alert("⚠️ Por favor ingrese el Link de Salesforce."); return; }
            if(!$('#tipoCredito').val()) { alert("⚠️ Debe seleccionar el Tipo de Crédito."); return; }
            const plazoSeleccionado = $('#plazoCredito').val();
            if(!plazoSeleccionado) { alert("⚠️ Debe seleccionar un Plazo."); return; }

            $('#confCliente').text($('#nombreCliente').val());
            $('#confVehiculo').text($('#marcaVehiculo').val() + " " + $('#modeloVehiculo').val());
            $('#confTipoCredito').text($('#tipoCredito').val());
            $('#confPlazo').text(plazoSeleccionado);
            new bootstrap.Modal(document.getElementById('modalConfirmacionEnvio')).show();
        }

        async function enviarSolicitudReal() {
            const btn = document.getElementById('btnConfirmarEnvio');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ENVIANDO...';

            try {
                const [archivoCarpeta, archivoGlosa] = await Promise.all([
                    leerArchivo('inputCarpeta'),
                    leerArchivo('inputGlosa')
                ]);

                const datosPravia = {
                    sucursal: $('#sucursal').val(),
                    cliente: {
                        nombre: $('#nombreCliente').val(), rut: $('#rutCliente').val(), email: $('#emailCliente').val(),
                        telefono: $('#telefonoCliente').val(), direccion: $('#direccionCliente').val(), comuna: $('#comuna').val(),
                        tipoTrabajador: $('#tipoTrabajador').val(), antiguedad: $('#antiguedad').val(), sueldo: $('#sueldoLiquido').val(),
                        rutEmpleador: $('#rutEmpleador').val(), nombreEmpleador: $('#nombreEmpleador').val()
                    },
                    vehiculo: {
                        anio: $('#anioVehiculo').val(), marca: $('#marcaVehiculo').val(), modelo: $('#modeloVehiculo').val(),
                        patente: $('#patente').val(), precio: $('#precioVehiculo').val()
                    },
                    credito: {
                        plazo: $('#plazoCredito').val(), tipo: $('#tipoCredito').val(), pie: $('#inputPie').val(),
                        financiar: $('#montoFinanciar').val(), salesforce: $('#salesforceId').val(), comentarios: $('#comentarios').val()
                    },
                    archivo: archivoCarpeta,
                    archivoGlosa: archivoGlosa
                };

                const response = await fetch('index.php?action=ajax_pravia_submit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datosPravia)
                });
                
                const res = await response.json();

                bootstrap.Modal.getInstance(document.getElementById('modalConfirmacionEnvio')).hide();
                if (res.success) {
                    alert("✅ Solicitud enviada correctamente.\nID Generado: " + res.id);
                    resetFormAndGoBack();
                } else {
                    alert("❌ Error al enviar: " + res.message);
                }
                btn.disabled = false; btn.innerHTML = originalText;

            } catch (error) {
                console.error(error);
                bootstrap.Modal.getInstance(document.getElementById('modalConfirmacionEnvio')).hide();
                alert("Error procesando archivos: " + error.message);
                btn.disabled = false; btn.innerHTML = originalText;
            }
        }
    </script>
</body>
</html>
