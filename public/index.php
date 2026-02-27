<?php
require __DIR__ . '/db.php';

$title = "Commandes à réceptionner";
require __DIR__ . '/inc/header.php';

// Par défaut : uniquement les commandes non réceptionnées
$orders = $pdo->query("
    SELECT id, numero, contact, date_commande, statut, facture, created_at
    FROM orders
    WHERE statut != 'receptionnee'
    ORDER BY date_commande DESC
")->fetchAll();
?>

<h1>Commandes à réceptionner</h1>

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Contact</th>
            <th>Date</th>
            <th>Statut</th>
            <th>Facture</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td data-label="N°"><?= htmlspecialchars($o['numero']) ?></td>
            <td data-label="Contact"><?= htmlspecialchars($o['contact']) ?></td>
            <td data-label="Date"><?= htmlspecialchars($o['date_commande']) ?></td>
            <td data-label="Statut"><?= htmlspecialchars($o['statut']) ?></td>
            <td data-label="">
                <a class="button" href="commande.php?id=<?= $o['id'] ?>">Voir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/inc/footer.php'; ?>
