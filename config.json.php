<?php
header('Content-Type: application/json');

// On charge la config PHP existante
$config = require __DIR__ . '/config.php';

// On renvoie du JSON propre pour Python
echo json_encode([
    "db_host" => $config["db_host"],
    "db_user" => $config["db_user"],
    "db_pass" => $config["db_pass"],
    "db_name" => $config["db_name"],
]);
