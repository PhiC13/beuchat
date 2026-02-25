<?php
require __DIR__ . '/db.php';

$id = intval($_GET['id'] ?? 0);

$commande = $pdo->prepare("
    SELECT *
    FROM orders
    WHERE id = ?
");
$commande->execute([$id]);
$commande = $commande->fetch();

if (!$commande) {
    die("Commande introuvable");
}

$items = $pdo->prepare("
    SELECT *
    FROM order_items
    WHERE order_id = ?
    ORDER BY reference
");
$items->execute([$id]);
$items = $items->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails commande <?= htmlspecialchars($commande['numero']) ?></title>
</head>
<body>

<h1>Commande <?= htmlspecialchars($commande['numero']) ?></h1>

<p>
    Contact : <?= htmlspecialchars($commande['contact']) ?><br>
    Date : <?= htmlspecialchars($commande['date_commande']) ?><br>
    Statut : <?= htmlspecialchars($commande['statut']) ?><br>
    Facture : <?= htmlspecialchars($commande['facture']) ?><br>
</p>

<h2>Produits</h2>

<table border="1" cellpadding="6">
    <tr>
        <th>Référence</th>
        <th>Nom</th>
        <th>Qté commandée</th>
        <th>Qté reçue</th>
        <th>Statut</th>
        <th>Supprimé ?</th>
    </tr>

    <?php foreach ($items as $i): ?>
        <tr>
            <td><?= htmlspecialchars($i['reference']) ?></td>
            <td><?= htmlspecialchars($i['nom']) ?></td>
            <td><?= htmlspecialchars($i['quantite_commandee']) ?></td>
            <td><?= htmlspecialchars($i['quantite_recue']) ?></td>
            <td><?= htmlspecialchars($i['statut']) ?></td>
            <td>
                <?= $i['deleted_at'] ? "Oui ({$i['deleted_at']})" : "Non" ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<p><a href="index.php">Retour</a></p>

</body>
</html>
