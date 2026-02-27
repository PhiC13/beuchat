<?php
require __DIR__ . '/db.php';

// Récupération de tous les produits (toutes lignes de commande)
$items = $pdo->query("
    SELECT 
        oi.id,
        oi.order_id,
        oi.reference,
        oi.nom,
        oi.quantite_commandee,
        oi.quantite_recue,
        oi.statut,
        oi.created_at,
        oi.updated_at,
        o.numero AS numero_commande
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    ORDER BY oi.reference
")->fetchAll();

// Nom du fichier
$filename = "produits.csv";

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
    "Created_at",
    "Updated_at"
], ";");

// Lignes
foreach ($items as $i) {
    fputcsv($output, [
        $i['id'],
        $i['numero_commande'],
        $i['reference'],
        $i['nom'],
        $i['quantite_commandee'],
        $i['quantite_recue'],
        $i['statut'],
        $i['created_at'],
        $i['updated_at']
    ], ";");
}

fclose($output);
exit;
