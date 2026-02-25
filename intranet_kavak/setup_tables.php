<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

try {
    // 1. Create tables
    $db->exec("CREATE TABLE IF NOT EXISTS tips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titulo VARCHAR(255) NOT NULL,
        contenido TEXT NOT NULL,
        activo TINYINT(1) DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS solicitudes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        categoria VARCHAR(100) NOT NULL,
        titulo VARCHAR(255) NOT NULL,
        descripcion TEXT NOT NULL,
        estado ENUM('Pendiente', 'En Progreso', 'Resuelto', 'Cerrado') DEFAULT 'Pendiente',
        respuesta_admin TEXT DEFAULT NULL,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )");

    // Insert default tips if empty
    $stmt = $db->query("SELECT COUNT(*) FROM tips");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO tips (titulo, contenido) VALUES 
            ('¿Cómo destaco una tarea?', 'En el Escritorio (Inicio), debajo del Widget del clima, encontrarás el bloque \"Tareas Destacadas\". Puedes marcar las tareas que urgen haciendo clic en el checkbox. ¡Pronto se vincularán a tu gestor de tareas principal!'),
            ('Añadir herramientas favoritas', 'En el Escritorio, el bloque de Favoritos te da acceso rápido al Directorio. Es ideal para llegar rápidamente a páginas clave de la Intranet.')
        ");
        echo "Default tips inserted.\n";
    }

    // Replace sidebar in views
    $viewsDir = __DIR__ . '/views/';
    $files = glob($viewsDir . '*.php');

    $pattern = '/<aside\s+class="sidebar"[^>]*>.*?<\/aside>/is';
    $replacement = "<?php include 'partials/sidebar.php'; ?>";

    foreach ($files as $file) {
        $content = file_get_contents($file);
        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $replacement, $content);
            file_put_contents($file, $newContent);
            echo "Updated sidebar in " . basename($file) . "\n";
        }
    }
    echo "Setup complete.\n";
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
