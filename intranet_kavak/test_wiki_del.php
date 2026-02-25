<?php
require 'config/database.php';
try {
    $db = (new Database())->getConnection();

    // Test if we can delete wiki_articulos where id exists
    $stmt = $db->query("SELECT id FROM wiki_articulos LIMIT 1");
    $art = $stmt->fetch();
    if ($art) {
        $id = $art['id'];
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // try to execute the notification query First
        $db->prepare("DELETE FROM notificaciones WHERE tipo = 'wiki' AND enlace LIKE ?")->execute(["%id={$id}%"]);
        echo "Notification query passed.\n";

        $db->prepare("DELETE FROM wiki_articulos WHERE id = ?")->execute([$id]);
        echo "Wiki query passed.\n";

    }
    else {
        echo "No records to test.\n";
    }

}
catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
