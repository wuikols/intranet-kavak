<?php
require 'config/database.php';
try {
    $db = (new Database())->getConnection();
    // try to delete a topic
    $stmt = $db->query("SELECT id FROM foro_temas LIMIT 1");
    $row = $stmt->fetch();
    if ($row) {
        $id = $row['id'];
        $db->exec("DELETE FROM foro_temas WHERE id={$id}");
        echo "Deleted foro tema $id \n";
    }

    $stmt2 = $db->query("SELECT id FROM wiki_articulos LIMIT 1");
    $row2 = $stmt2->fetch();
    if ($row2) {
        $id2 = $row2['id'];
        $db->exec("DELETE FROM wiki_articulos WHERE id={$id2}");
        echo "Deleted wiki_articulos $id2 \n";
    }
}
catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
