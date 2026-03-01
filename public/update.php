<?php
require __DIR__ . '/inc/bootstrap.php';

// Log début
log_event($pdo, 'update_start', 'Début de mise à jour via bouton header');

// Racine du projet (un niveau au-dessus de /public)
$projectRoot = realpath(__DIR__ . '/..');

// Commande Python via Poetry
$cmd = 'cd ' . escapeshellarg($projectRoot) . ' && poetry run python script/update_db.py 2>&1';

// Exécution robuste avec exec()
$outputLines = [];
$returnVar = 0;
exec($cmd, $outputLines, $returnVar);

// Reconstituer la sortie complète
$output = implode("\n", $outputLines);

// Log debug systématique (toujours utile sur o2switch)
log_event($pdo, 'update_debug', 'Résultat brut exec()', [
    'cmd' => $cmd,
    'return_var' => $returnVar,
    'output' => $output,
]);

// Critères d’erreur robustes
$hasError = false;

// 1) Code retour ≠ 0 → échec
if ($returnVar !== 0) {
    $hasError = true;
}

// 2) Sortie vide → Python ne s’est probablement pas exécuté
if (trim($output) === '') {
    $hasError = true;
}

// 3) Sortie contenant "Traceback" → erreur Python
if (stripos($output, 'traceback') !== false) {
    $hasError = true;
}

// 4) Sortie contenant "Error" (insensible à la casse)
if (stripos($output, 'error') !== false) {
    $hasError = true;
}

if ($hasError) {
    log_event($pdo, 'update_error', 'Erreur lors de la mise à jour', [
        'return_var' => $returnVar,
        'output' => $output,
    ]);

    $msg = urlencode($output ?: "Erreur inconnue (code $returnVar)");
    header("Location: index.php?error=$msg");
    exit;
}

// Succès
log_event($pdo, 'update_end', 'Mise à jour terminée avec succès', [
    'return_var' => $returnVar,
    'output' => $output,
]);

header("Location: index.php?updated=1");
exit;
