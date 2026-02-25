<?php
require 'config/database.php';
try {
    $db = (new Database())->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if we can delete an article
    $stmt = $db->query("SELECT id FROM wiki_articulos LIMIT 1");
    $art = $stmt->fetch();
    if ($art) {
        echo "Trying to delete wiki article ID " . $art['id'] . "\n";
        try {
            $db->exec("DELETE FROM wiki_articulos WHERE id=" . $art['id']);
            echo "Successfully deleted wiki article.\n";
        }
        catch (Exception $e) {
            echo "Failed to delete wiki article: " . $e->getMessage() . "\n";
        }
    }
    else {
        echo "No wiki articles found.\n";
    }

    // Check if we can delete a forum topic
    $stmt2 = $db->query("SELECT id FROM foro_temas LIMIT 1");
    $topic = $stmt2->fetch();
    if ($topic) {
        echo "Trying to delete forum topic ID " . $topic['id'] . "\n";
        try {
            $db->exec("DELETE FROM foro_temas WHERE id=" . $topic['id']);
            echo "Successfully deleted forum topic.\n";
        }
        catch (Exception $e) {
            echo "Failed to delete forum topic: " . $e->getMessage() . "\n";
        }
    }
    else {
        echo "No forum topics found.\n";
    }

}
catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
