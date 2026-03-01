<?php
require __DIR__ . '/inc/bootstrap.php';

// Log début
log_event($pdo, 'update_start', 'Début de mise à jour via bouton header');

// Racine du projet (utile pour Windows)
$projectRoot = realpath(__DIR__ . '\\..');

// Détection OS
$isWindows = (PHP_OS_FAMILY === 'Windows');

if ($isWindows) {

    // --- WINDOWS (LOCAL) ---
    // Python Poetry Windows
    $python = 'C:\\Users\\phic1\\AppData\\Local\\pypoetry\\Cache\\virtualenvs\\beuchat-reception-f00p9s3A-py3.14\\Scripts\\python.exe';

    // Commande Windows (avec cd + chemins relatifs)
    $cmd = 'cd "' . $projectRoot . '" && "' . $python . '" script\\update_db.py 2>&1';

} else {

    // --- LINUX (o2switch) ---
    // Chemin Python o2switch
    $python = '/home/vapu2355/virtualenv/intranet.lebateaujaune.com/beuchat/3.11/bin/python';

    // Chemin ABSOLU vers update_db.py
    $script = '/home/vapu2355/intranet.lebateaujaune.com/beuchat/script/update_db.py';

    // Commande Linux (sans cd, sans chemins relatifs)
    $cmd = $python . ' ' . $script . ' 2>&1';
}

// Exécution robuste
$outputLines = [];
$returnVar = 0;
exec($cmd, $outputLines, $returnVar);
$output = implode("\n", $outputLines);

// Log debug
log_event($pdo, 'update_debug', 'Résultat brut exec()', [
    'cmd' => $cmd,
    'return_var' => $returnVar,
    'output' => $output,
]);

// Détection d’erreur
$hasError = false;

if ($returnVar !== 0) $hasError = true;
if (trim($output) === '') $hasError = true;
if (stripos($output, 'traceback') !== false) $hasError = true;
if (stripos($output, 'error') !== false) $hasError = true;

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
