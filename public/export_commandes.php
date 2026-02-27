<?php
require __DIR__ . '/db.php';

// Récupération de toutes les commandes
$orders = $pdo->query("
    SELECT id, numero, contact, date_commande, statut, created_at, updated_at
    FROM orders
    ORDER BY date_commande DESC
")->fetchAll();

// Nom du fichier
$filename = "commandes.csv";

// Headers HTTP
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Flux CSV
$output = fopen("php://output", "w");

// BOM UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes
fputcsv($output, [
    "ID", "Numero", "Contact", "Date commande", "Statut", "Created_at", "Updated_at"
], ";");

// Lignes
foreach ($orders as $o) {
    fputcsv($output, [
        $o['id'],
        $o['numero'],
        $o['contact'],
        $o['date_commande'],
        $o['statut'],
        $o['created_at'],
        $o['updated_at']
    ], ";");
}

fclose($output);
exit;
