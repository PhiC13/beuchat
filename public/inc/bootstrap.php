<?php

// Connexion DB
require_once __DIR__ . '/../db.php';

// Logger
require_once __DIR__ . '/logger.php';

// Helpers (optionnel pour la suite)
if (file_exists(__DIR__ . '/utils.php')) {
    require_once __DIR__ . '/utils.php';
}

// Exemple : log automatique de chaque page vue
log_event($pdo, 'page_view', 'Page consultée', [
    'page' => $_SERVER['REQUEST_URI'] ?? null,
    'ip'   => $_SERVER['REMOTE_ADDR'] ?? null,
]);
