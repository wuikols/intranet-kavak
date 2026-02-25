<?php
require 'config/database.php';
require 'models/Forum.php';
$db = (new Database())->getConnection();
$f = new Forum($db);
$res = $f->deleteTopic(1);
if ($res)
    echo "DELETED\n";
else
    echo "FAILED\n";
