<?php 
// No se requiere lógica PHP compleja aquí, solo mostrar la vista y errores si los hay.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Kavak OS</title>
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

        /* LAYOUT DE PANTALLA DIVIDIDA (SPLIT SCREEN) */
        .login-wrapper { display: grid; grid-template-columns: 1fr 1fr; height: 100vh; }

        /* LADO IZQUIERDO: BRANDING E IMAGEN */
        .brand-side {
            position: relative;
            background: linear-gradient(135deg, rgba(15,23,42,0.9) 0%, rgba(37,99,235,0.8) 100%), url('/intranet_kavak/assets/img/Kavak_Lerma.jpeg');
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

        /* LADO DERECHO: FORMULARIO */
        .form-side {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background: #ffffff;
        }
        .form-container { width: 100%; max-width: 420px; padding: 20px; }
        
        .login-header { margin-bottom: 40px; text-align: center; }
        /* Ajuste de tamaño para el logo negro */
        .login-logo { width: 180px; height: auto; margin-bottom: 30px; }
        .login-title { font-size: 28px; font-weight: 800; color: var(--text-main); margin-bottom: 10px; letter-spacing: -0.5px; }
        .login-subtitle { color: var(--text-secondary); font-size: 15px; }

        /* COMPONENTES DEL FORMULARIO */
        .form-group { margin-bottom: 25px; }
        .form-label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: var(--text-main); }
        .input-group { position: relative; }
        .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-secondary); font-size: 16px; }
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
        .form-input::placeholder { color: #A1A1AA; }

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
        }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 8px 15px rgba(37, 99, 235, 0.25); }

        .form-footer { margin-top: 25px; text-align: center; font-size: 14px; color: var(--text-secondary); }
        .form-footer a { color: var(--accent-color); font-weight: 600; text-decoration: none; }
        .form-footer a:hover { text-decoration: underline; }

        .alert { padding: 15px; border-radius: 10px; margin-bottom: 25px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px; }
        .alert-danger { background: #FEE2E2; color: #991B1B; border: 1px solid #F87171; }
        .alert-success { background: #DCFCE7; color: #166534; border: 1px solid #4ADE80; }

        @media (max-width: 992px) {
            .login-wrapper { grid-template-columns: 1fr; }
            .brand-side { display: none; }
            .form-side { padding: 20px; height: 100vh; align-items: flex-start; padding-top: 60px; }
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
                <h1>Tu ecosistema digital de trabajo.</h1>
                <p>Accede a todas las herramientas, comunicaciones y procesos de Kavak en una sola plataforma unificada y segura.</p>
            </div>
            <div class="brand-footer">
                <span><i class="fas fa-shield-alt"></i> Acceso Seguro Corporativo</span>
                <span><i class="fas fa-bolt"></i> Kavak OS v2.0</span>
            </div>
        </div>

        <div class="form-side">
            <div class="form-container">
                <div class="login-header">
                    <img src="/intranet_kavak/assets/img/LogoLetraNegra.png" alt="Kavak Logo" class="login-logo">
                    <h2 class="login-title">¡Hola de nuevo!</h2>
                    <p class="login-subtitle">Ingresa tus credenciales corporativas para continuar.</p>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
                <?php endif; ?>

                <form action="index.php?action=login" method="POST">
                    <div class="form-group">
                        <label class="form-label">Correo Electrónico</label>
                        <div class="input-group">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="form-input" placeholder="nombre@kavak.com" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Contraseña</label>
                        <div class="input-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-input" placeholder="••••••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">Acceder a la Plataforma</button>

                    <div class="form-footer">
                        <p>¿Olvidaste tu contraseña? <a href="index.php?action=forgot_password">Recuperar acceso</a></p>
                        <p style="margin-top: 15px;">¿No tienes cuenta? <a href="index.php?action=register">Solicitar acceso a RRHH</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>