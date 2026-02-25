<?php 
// Vista aislada para el registro/solicitud de cuenta.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Kavak OS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --accent-color: #2563EB;
            --accent-hover: #1D4ED8;
            --text-main: #111827;
            --text-secondary: #6B7280;
            --input-bg: #F9FAFB;
            --border-subtle: #E5E7EB;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { height: 100vh; overflow: hidden; background: #fff; }

        /* LAYOUT SPLIT SCREEN */
        .login-wrapper { display: grid; grid-template-columns: 1fr 1fr; height: 100vh; }

        /* BRANDING SIDE (Izquierda) */
        .brand-side {
            position: relative;
            background: linear-gradient(135deg, rgba(15,23,42,0.95) 0%, rgba(37,99,235,0.85) 100%), url('/intranet_kavak/assets/img/Kavak_Lerma.jpeg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 60px;
            color: white;
        }
        .brand-content { position: relative; z-index: 2; max-width: 500px; }
        .brand-content h1 { font-size: 42px; font-weight: 800; line-height: 1.1; margin-bottom: 20px; letter-spacing: -1px; }
        .brand-content p { font-size: 18px; opacity: 0.9; line-height: 1.6; font-weight: 500; }
        .brand-footer { margin-top: 40px; font-size: 14px; opacity: 0.7; display: flex; gap: 20px; align-items: center;}
        .brand-footer i { font-size: 18px; }

        /* FORM SIDE (Derecha) */
        .form-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #ffffff;
            overflow-y: auto; /* Por si la pantalla es muy pequeña */
        }
        .form-container { width: 100%; max-width: 420px; padding: 20px; }
        
        .login-header { margin-bottom: 35px; text-align: center; }
        .login-logo { width: 160px; height: auto; margin-bottom: 25px; }
        .login-title { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 8px; letter-spacing: -0.5px; }
        .login-subtitle { color: var(--text-secondary); font-size: 14px; line-height: 1.5; }

        /* COMPONENTES DEL FORMULARIO */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: var(--text-main); }
        .input-group { position: relative; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 16px; transition: 0.3s; }
        
        .form-input { 
            width: 100%; 
            padding: 14px 16px 14px 45px; 
            border: 2px solid var(--border-subtle); 
            border-radius: 12px; 
            font-size: 15px; 
            background: var(--input-bg); 
            color: var(--text-main); 
            transition: all 0.3s ease; 
            outline: none; 
            font-weight: 500;
        }
        .form-input:focus { border-color: var(--accent-color); background: #fff; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1); }
        .form-input:focus + .input-icon { color: var(--accent-color); }
        .form-input::placeholder { color: #A1A1AA; font-weight: 400; }

        .btn-primary { 
            width: 100%; 
            padding: 16px; 
            background: var(--accent-color); 
            color: white; 
            border: none; 
            border-radius: 12px; 
            font-size: 16px; 
            font-weight: 700; 
            cursor: pointer; 
            transition: all 0.2s; 
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.25); }

        .form-footer { margin-top: 25px; text-align: center; font-size: 14px; font-weight: 600; }
        .form-footer a { color: var(--text-secondary); text-decoration: none; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px;}
        .form-footer a:hover { color: var(--text-main); }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border: 1px solid #F87171; }

        @media (max-width: 992px) {
            body { overflow-y: auto; }
            .login-wrapper { grid-template-columns: 1fr; height: auto; min-height: 100vh; }
            .brand-side { display: none; }
            .form-side { padding: 20px; align-items: center; padding-top: 40px; }
            .login-title { font-size: 24px; }
        }
    </style>
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="login-wrapper">
        <div class="brand-side">
            <div class="brand-content">
                <img src="/intranet_kavak/assets/img/LogoLetraBlanca.png" alt="Kavak OS" style="width: 160px; margin-bottom: 30px;">
                <h1>Únete al equipo.</h1>
                <p>Solicita tu acceso al ecosistema digital de Kavak. Conecta con tus compañeros, accede a recursos exclusivos y mantente al día con las novedades de la empresa.</p>
            </div>
            <div class="brand-footer">
                <span><i class="fas fa-users"></i> Colaboración Activa</span>
                <span><i class="fas fa-rocket"></i> Crecimiento Continuo</span>
            </div>
        </div>

        <div class="form-side">
            <div class="form-container">
                <div class="login-header">
                    <img src="/intranet_kavak/assets/img/LogoLetraNegra.png" alt="Kavak Logo" class="login-logo">
                    <h2 class="login-title">Crear Cuenta</h2>
                    <p class="login-subtitle">Ingresa tus datos institucionales para solicitar tu alta en la plataforma.</p>
                </div>

                <?php if (isset($error) && !empty($error)): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="index.php?action=register" method="POST">
                    
                    <div class="form-group">
                        <label class="form-label">RUT Oficial</label>
                        <div class="input-group">
                            <input type="text" name="rut" class="form-input rut-input" placeholder="Ej: 12.345.678-9" required autofocus>
                            <i class="fas fa-id-card input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Correo Corporativo</label>
                        <div class="input-group">
                            <input type="email" name="email" class="form-input" placeholder="nombre.apellido@kavak.com" required>
                            <i class="fas fa-envelope input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contraseña de Acceso</label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-input" placeholder="Mínimo 6 caracteres" minlength="6" required>
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-user-plus"></i> Registrar Usuario
                    </button>

                    <div class="form-footer">
                        <a href="index.php?action=login"><i class="fas fa-arrow-left"></i> Volver al Acceso Principal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function formatRut(input) { 
            let r = input.value.replace(/[^0-9kK]/g, '').toUpperCase(); 
            if (r.length > 1) { 
                input.value = r.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, ".") + '-' + r.slice(-1); 
            } else { 
                input.value = r; 
            } 
        }
        document.querySelector('.rut-input').addEventListener('input', function() { formatRut(this); });
    </script>
</body>
</html>