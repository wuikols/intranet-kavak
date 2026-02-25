<?php
require 'config/database.php';
require 'models/Wiki.php';
$db = (new Database())->getConnection();
$wiki = new Wiki($db);
$res = $wiki->deleteArticle(8);
if ($res)
    echo "DELETED\n";
else {
    echo "FAILED\n";
}
