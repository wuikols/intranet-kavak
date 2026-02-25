<?php
require 'config/database.php';
$db = (new Database())->getConnection();
try {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->query("SELECT * FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'foro_temas'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
