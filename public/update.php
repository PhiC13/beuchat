<?php
require __DIR__ . '/inc/bootstrap.php';

// Log début
log_event($pdo, 'update_start', 'Début de mise à jour via bouton header');

// Commande Python via Poetry
$cmd = 'cd ' . escapeshellarg(__DIR__ . '/..') . ' && poetry run python script/update_db.py 2>&1';

// Exécution
$output = shell_exec($cmd);

// Si le script renvoie quelque chose contenant "Error" ou est vide
if ($output === null || stripos($output, 'error') !== false) {

    log_event($pdo, 'update_error', 'Erreur lors de la mise à jour', [
        'output' => $output
    ]);

    $msg = urlencode($output ?: "Erreur inconnue");
    header("Location: index.php?error=$msg");
    exit;
}

// Log succès
log_event($pdo, 'update_end', 'Mise à jour terminée avec succès', [
    'output' => $output
]);

header("Location: index.php?updated=1");
exit;
