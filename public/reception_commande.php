<?php
require __DIR__ . '/inc/bootstrap.php';

$order_id = intval($_POST['order_id'] ?? 0);
$recues = $_POST['recue'] ?? [];

if ($order_id <= 0) {
    die("Commande invalide");
}

/* ---------------------------------------------------------
   1. Récupération des lignes de commande
--------------------------------------------------------- */
$items = $pdo->prepare("
    SELECT id, quantite_commandee, quantite_recue
    FROM order_items
    WHERE order_id = ?
");
$items->execute([$order_id]);
$items = $items->fetchAll();

/* ---------------------------------------------------------
   2. Préparation des requêtes
--------------------------------------------------------- */
$update_item = $pdo->prepare("
    UPDATE order_items
    SET quantite_recue = ?, 
        statut = ?, 
        updated_at = NOW()
    WHERE id = ?
");

$insert_reception = $pdo->prepare("
    INSERT INTO receptions (order_item_id, quantite, utilisateur)
    VALUES (?, ?, ?)
");

$changes = []; // Pour le log métier

/* ---------------------------------------------------------
   3. Mise à jour des lignes
--------------------------------------------------------- */
foreach ($items as $item) {

    $item_id = $item['id'];
    $qte_cmd = intval($item['quantite_commandee']);
    $qte_old = intval($item['quantite_recue']);
    $qte_new = isset($recues[$item_id]) ? intval($recues[$item_id]) : $qte_old;

    if ($qte_new < 0) $qte_new = 0;
    if ($qte_new > $qte_cmd) $qte_new = $qte_cmd;

    // Détermination du statut
    if ($qte_new == 0) {
        $statut = "en_attente";
    } elseif ($qte_new < $qte_cmd) {
        $statut = "partielle";
    } else {
        $statut = "receptionnee";
    }

    // Si changement → log + insertion dans receptions
    if ($qte_new !== $qte_old) {

        $changes[] = [
            'item_id' => $item_id,
            'old' => $qte_old,
            'new' => $qte_new,
            'statut' => $statut
        ];

        // Quantité reçue à cet instant
        $delta = $qte_new - $qte_old;

        $insert_reception->execute([
            $item_id,
            $delta,
            $_SERVER['REMOTE_USER'] ?? 'inconnu'
        ]);
    }

    // Mise à jour de la ligne
    $update_item->execute([$qte_new, $statut, $item_id]);
}

/* ---------------------------------------------------------
   4. Mise à jour du statut global de la commande
--------------------------------------------------------- */
$stats = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN statut = 'receptionnee' THEN 1 ELSE 0 END) AS lignes_ok,
        COUNT(*) AS total
    FROM order_items
    WHERE order_id = ?
");
$stats->execute([$order_id]);
$stats = $stats->fetch();

if ($stats['lignes_ok'] == 0) {
    $statut_commande = "en attente";
} elseif ($stats['lignes_ok'] < $stats['total']) {
    $statut_commande = "partielle";
} else {
    $statut_commande = "receptionnée";
}

$update_order = $pdo->prepare("
    UPDATE orders
    SET statut = ?, updated_at = NOW()
    WHERE id = ?
");
$update_order->execute([$statut_commande, $order_id]);

/* ---------------------------------------------------------
   5. Log métier global
--------------------------------------------------------- */
log_event($pdo, 'update_reception', 'Mise à jour de la réception commande', [
    'order_id' => $order_id,
    'statut_final' => $statut_commande,
    'modifications' => $changes,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
]);

/* ---------------------------------------------------------
   6. Redirection
--------------------------------------------------------- */
header("Location: commande.php?id=" . $order_id);
exit;
