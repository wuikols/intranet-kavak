<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - Kavak OS</title> <link rel="stylesheet" href="/intranet_kavak/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="icon" href="https://www.kavak.com/favicon.ico" type="image/x-icon">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <h2 class="login-title" style="color: #059669;">Identidad Verificada</h2>
            <p class="login-subtitle">Crea tu nueva credencial de acceso</p>
            <?php if(isset($error)) echo "<div class='error-msg'>$error</div>"; ?>
            <form action="index.php?action=process_reset" method="POST">
                <div class="form-group"><label>Nueva Contraseña</label><input type="password" name="password" required placeholder="Mínimo 6 caracteres"></div>
                <div class="form-group"><label>Confirmar Contraseña</label><input type="password" name="confirm_password" required placeholder="Repite la contraseña"></div>
                <button type="submit" class="btn-login">Guardar y Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>