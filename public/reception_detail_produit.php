<?php
require __DIR__ . '/inc/bootstrap.php';

$ref = $_GET['ref'] ?? '';
$ref = trim($ref);

if ($ref === '') {
    die("Référence invalide");
}

/* ---------------------------------------------------------
   1. Infos produit
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
   2. Historique des réceptions (toutes commandes)
--------------------------------------------------------- */
$stmt = $pdo->prepare("
    SELECT 
        r.*,
        oi.nom AS nom_item,
        oi.reference,
        oi.order_id,
        o.numero AS numero_commande,
        o.date_commande
    FROM receptions r
    JOIN order_items oi ON oi.id = r.order_item_id
    JOIN orders o ON o.id = oi.order_id
    WHERE oi.reference = ?
    ORDER BY r.date_reception DESC
");
$stmt->execute([$ref]);
$receptions = $stmt->fetchAll();

/* ---------------------------------------------------------
   3. Log consultation
--------------------------------------------------------- */
log_event($pdo, 'view_product_receptions', 'Consultation historique réceptions produit', [
    'reference' => $ref,
    'nb_receptions' => count($receptions),
]);

$title = "Historique réceptions – " . htmlspecialchars($ref);
require __DIR__ . '/inc/header.php';
?>

<h1>Historique des réceptions</h1>

<p>
    <strong><?= htmlspecialchars($ref) ?></strong><br>
    <?= htmlspecialchars($nom_produit) ?>
</p>

<p>
    <a href="produit.php?ref=<?= urlencode($ref) ?>" 
       class="button" 
       style="background:#eee; color:#333; padding:6px 10px; font-size:0.85em;">
        ← Retour produit
    </a>
</p>

<h2>
    <?= count($receptions) ?> réception(s) enregistrée(s)
</h2>

<?php if (count($receptions) === 0): ?>

    <p>Aucune réception enregistrée pour ce produit.</p>

<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Commande</th>
            <th>Qté reçue</th>
            <th>Utilisateur</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($receptions as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['date_reception']) ?></td>
            <td>
                <a href="commande.php?id=<?= $r['order_id'] ?>">
                    <?= htmlspecialchars($r['numero_commande']) ?>
                </a>
            </td>
            <td><?= htmlspecialchars($r['quantite']) ?></td>
            <td><?= htmlspecialchars($r['utilisateur'] ?? '—') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<?php require __DIR__ . '/inc/footer.php'; ?>
<?php component_footer(); ?>
