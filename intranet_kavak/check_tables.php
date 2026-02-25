<?php
require 'config/database.php';
$db = (new Database())->getConnection();
try {
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->query("SHOW TABLES LIKE 'foro_%'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
