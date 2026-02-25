<?php
require_once 'config/database.php';

try {
    $db = (new Database())->getConnection();
    $sql = "ALTER TABLE roles ADD COLUMN p_roles BOOLEAN DEFAULT FALSE;";
    $db->exec($sql);
    echo "Column p_roles added successfully.";
}
catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.";
    }
    else {
        echo "Error: " . $e->getMessage();
    }
}
?>
