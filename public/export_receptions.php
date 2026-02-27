<?php
require __DIR__ . '/db.php';

// Récupération des réceptions
$receptions = $pdo->query("
    SELECT 
        oi.id,
        oi.order_id,
        o.numero AS numero_commande,
        oi.reference,
        oi.nom,
        oi.quantite_commandee,
        oi.quantite_recue,
        oi.statut,
        oi.updated_at
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE oi.quantite_recue > 0
    ORDER BY oi.updated_at DESC
")->fetchAll();

// Nom du fichier
$filename = "receptions.csv";

// Headers HTTP
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");

// Flux CSV
$output = fopen("php://output", "w");

// BOM UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes
fputcsv($output, [
    "ID ligne",
    "Commande",
    "Reference",
    "Nom",
    "Quantite commandee",
    "Quantite recue",
    "Statut",
    "Date reception"
], ";");

// Lignes
foreach ($receptions as $r) {
    fputcsv($output, [
        $r['id'],
        $r['numero_commande'],
        $r['reference'],
        $r['nom'],
        $r['quantite_commandee'],
        $r['quantite_recue'],
        $r['statut'],
        $r['updated_at']
    ], ";");
}

fclose($output);
exit;
