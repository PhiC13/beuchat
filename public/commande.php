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

$title = "Commande " . htmlspecialchars($commande['numero']);
require __DIR__ . '/inc/header.php';
?>

<h1>Commande <?= htmlspecialchars($commande['numero']) ?></h1>

<p>
    <strong>Contact :</strong> <?= htmlspecialchars($commande['contact']) ?><br>
    <strong>Date :</strong> <?= htmlspecialchars($commande['date_commande']) ?><br>
    <strong>Statut :</strong> <?= htmlspecialchars($commande['statut']) ?><br>
</p>

<h2>Produits</h2>

<form action="reception_commande.php" method="POST">
    <input type="hidden" name="order_id" value="<?= $commande['id'] ?>">

    <table>
        <thead>
            <tr>
                <th>Réf</th>
                <th>Nom</th>
                <th>Qté cmd</th>
                <th>Qté reçue</th>
                <th>Statut</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($items as $i): ?>
            <tr>
                <td data-label="Réf"><?= htmlspecialchars($i['reference']) ?></td>
                <td data-label="Nom"><?= htmlspecialchars($i['nom']) ?></td>
                <td data-label="Qté cmd"><?= htmlspecialchars($i['quantite_commandee']) ?></td>

                <td data-label="Qté reçue">
                    <input type="number"
                           name="recue[<?= $i['id'] ?>]"
                           value="<?= htmlspecialchars($i['quantite_recue']) ?>"
                           min="0"
                           max="<?= htmlspecialchars($i['quantite_commandee']) ?>"
                           style="width: 70px;">
                </td>

                <td data-label="Statut">
                    <?= htmlspecialchars($i['statut']) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br>

    <button type="submit">Mettre à jour la réception</button>
</form>

<p><a class="button" href="index.php">Retour</a></p>

<?php require __DIR__ . '/inc/footer.php'; ?>
