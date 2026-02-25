<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Updates
    $queries = [
        "UPDATE noticias SET imagen_url = REPLACE(imagen_url, '/intranet_kavak/', '/') WHERE imagen_url LIKE '/intranet_kavak/%'",
        "UPDATE noticias SET curso_imagen = REPLACE(curso_imagen, '/intranet_kavak/', '/') WHERE curso_imagen LIKE '/intranet_kavak/%'",
        "UPDATE wiki_articulos SET imagen_portada = REPLACE(imagen_portada, '/intranet_kavak/', '/') WHERE imagen_portada LIKE '/intranet_kavak/%'",
        "UPDATE wiki_articulos SET archivo_adjunto = REPLACE(archivo_adjunto, '/intranet_kavak/', '/') WHERE archivo_adjunto LIKE '/intranet_kavak/%'",
        "UPDATE foro_temas SET imagen = REPLACE(imagen, '/intranet_kavak/', '/') WHERE imagen LIKE '/intranet_kavak/%'"
    ];

    $count = 0;
    foreach ($queries as $q) {
        $stmt = $conn->prepare($q);
        $stmt->execute();
        $count += $stmt->rowCount();
        echo "Executed: $q (Rows affected: " . $stmt->rowCount() . ")\n";
    }

    echo "\nTotal rows patched in database: $count\n\nAll Database URLs fixed!\n";

}
catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
