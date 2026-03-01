<?php
require __DIR__ . '/inc/bootstrap.php';

$ref = $_GET['ref'] ?? '';
$ref = trim($ref);

if ($ref === '') {
    die("Référence produit invalide");
}

/* ---------------------------------------------------------
   1. Récupération des infos produit (nom)
--------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT nom
    FROM order_items
    WHERE reference = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$ref]);
$prod = $stmt->fetch();

if (!$prod) {
    die("Produit introuvable");
}

$nom_produit = $prod['nom'];

/* ---------------------------------------------------------
   2. Récupération des lignes de commandes contenant ce produit
--------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT 
        oi.id AS item_id,
        oi.quantite_commandee,
        oi.quantite_recue,
        oi.statut AS statut_ligne,
        o.id AS order_id,
        o.numero AS numero_commande,
        o.contact,
        o.date_commande,
        o.statut AS statut_commande
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE oi.reference = ?
    ORDER BY o.date_commande DESC
");
$stmt->execute([$ref]);
$items = $stmt->fetchAll();

/* ---------------------------------------------------------
   3. Log consultation
--------------------------------------------------------- */
log_event($pdo, 'view_product_detail', 'Consultation du détail produit', [
    'reference' => $ref,
    'nb_lignes' => count($items),
]);

$title = "Produit : " . htmlspecialchars($ref);
require __DIR__ . '/inc/header.php';
?>

<h1>Produit : <?= htmlspecialchars($ref) ?></h1>
<p><strong><?= htmlspecialchars($nom_produit) ?></strong></p>

<h2>Présent dans <?= count($items) ?> commande(s)</h2>

<table>
    <thead>
        <tr>
            <th>Commande</th>
            <th>Contact</th>
            <th>Date</th>
            <th>Qté cmd</th>
            <th>Qté reçue</th>
            <th>Statut ligne</th>
            <th>Statut commande</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($items as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['numero_commande']) ?></td>
            <td><?= htmlspecialchars($i['contact']) ?></td>
            <td><?= htmlspecialchars($i['date_commande']) ?></td>
            <td><?= intval($i['quantite_commandee']) ?></td>
            <td><?= intval($i['quantite_recue']) ?></td>
            <td><?= htmlspecialchars($i['statut_ligne']) ?></td>
            <td><?= htmlspecialchars($i['statut_commande']) ?></td>
            <td>
                <a class="button" href="commande.php?id=<?= $i['order_id'] ?>">Voir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p><a class="button" href="produits.php">Retour</a></p>

<?php require __DIR__ . '/inc/footer.php'; ?>
