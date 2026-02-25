<?php
require __DIR__ . '/db.php';

$orders = $pdo->query("
    SELECT id, numero, contact, date_commande, statut, facture, created_at
    FROM orders
    ORDER BY date_commande DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Commandes Beuchat</title>
</head>
<body>

<h1>Commandes</h1>

<table border="1" cellpadding="6">
    <tr>
        <th>N°</th>
        <th>Contact</th>
        <th>Date</th>
        <th>Statut</th>
        <th>Facture</th>
        <th></th>
    </tr>

    <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= htmlspecialchars($o['numero']) ?></td>
            <td><?= htmlspecialchars($o['contact']) ?></td>
            <td><?= htmlspecialchars($o['date_commande']) ?></td>
            <td><?= htmlspecialchars($o['statut']) ?></td>
            <td><?= htmlspecialchars($o['facture']) ?></td>
            <td>
                <a href="commande.php?id=<?= $o['id'] ?>">Voir</a>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>
