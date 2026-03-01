<?php
require __DIR__ . '/inc/bootstrap.php';

$item_id = intval($_GET['id'] ?? 0);

if ($item_id <= 0) {
    die("Item invalide");
}

/* ---------------------------------------------------------
   1. Récupération des infos du produit
--------------------------------------------------------- */
$item = $pdo->prepare("
    SELECT oi.*, o.numero AS order_numero, o.id AS order_id
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE oi.id = ?
");
$item->execute([$item_id]);
$item = $item->fetch();

if (!$item) {
    die("Produit introuvable");
}

/* ---------------------------------------------------------
   2. Récupération de l’historique des réceptions
--------------------------------------------------------- */
$receptions = $pdo->prepare("
    SELECT *
    FROM receptions
    WHERE order_item_id = ?
    ORDER BY date_reception DESC
");
$receptions->execute([$item_id]);
$receptions = $receptions->fetchAll();

/* ---------------------------------------------------------
   3. Header
--------------------------------------------------------- */
$title = "Historique des réceptions – " . htmlspecialchars($item['reference']);
require __DIR__ . '/inc/header.php';
?>

<h1 class="section-header">
    <span>
        Historique – <?= htmlspecialchars($item['reference']) ?>
        <small style="font-size: 0.7em; opacity: 0.7;">
            (<?= count($receptions) ?> réceptions)
        </small>
    </span>

	<a href="produit.php?ref=<?= urlencode($item['reference']) ?>"
	class="button"
	style="background:#eee; color:#333; padding:6px 10px; font-size:0.85em;">
		← Retour produit
	</a>

</h1>

<p>
    <strong>Commande :</strong> <?= htmlspecialchars($item['order_numero']) ?><br>
    <strong>Produit :</strong> <?= htmlspecialchars($item['nom']) ?><br>
    <strong>Quantité commandée :</strong> <?= htmlspecialchars($item['quantite_commandee']) ?><br>
    <strong>Quantité reçue actuelle :</strong> <?= htmlspecialchars($item['quantite_recue']) ?><br>
</p>

<?php if (count($receptions) === 0): ?>

    <p>Aucune réception enregistrée pour ce produit.</p>

<?php else: ?>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Quantité reçue</th>
            <th>Utilisateur</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($receptions as $r): ?>
        <tr>
            <td><?= htmlspecialchars($r['date_reception']) ?></td>
            <td><?= htmlspecialchars($r['quantite']) ?></td>
            <td><?= htmlspecialchars($r['utilisateur'] ?? '—') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?>

<p><a class="button" href="commande.php?id=<?= $item['order_id'] ?>">Retour</a></p>

<?php require __DIR__ . '/inc/footer.php'; ?>
<?php component_footer(); ?>
