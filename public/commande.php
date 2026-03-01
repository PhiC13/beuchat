<?php
require __DIR__ . '/inc/bootstrap.php';

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

// Log consultation commande
log_event($pdo, 'view_order', 'Consultation d’une commande', [
    'order_id' => $id,
]);

/* ---------------------------------------------------------
   1. Construction dynamique des filtres
--------------------------------------------------------- */
$where = ["order_id = ?"];
$params = [$id];

// Recherche texte (référence ou nom)
if (!empty($_GET['q'])) {
    $where[] = "(reference LIKE ? OR nom LIKE ?)";
    $params[] = "%" . $_GET['q'] . "%";
    $params[] = "%" . $_GET['q'] . "%";
}

// Filtre statut
if (!empty($_GET['statut'])) {
    $where[] = "statut = ?";
    $params[] = $_GET['statut'];
}

/* ---------------------------------------------------------
   2. Requête SQL dynamique
--------------------------------------------------------- */
$sql = "
    SELECT *
    FROM order_items
    WHERE " . implode(" AND ", $where) . "
    ORDER BY reference
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$title = "Commande " . htmlspecialchars($commande['numero']);
require __DIR__ . '/inc/header.php';
?>

<h1>Commande <?= htmlspecialchars($commande['numero']) ?></h1>

<p>
    <strong>Contact :</strong> <?= htmlspecialchars($commande['contact']) ?><br>
    <strong>Date :</strong> <?= htmlspecialchars($commande['date_commande']) ?><br>
    <strong>Statut :</strong> <?= htmlspecialchars($commande['statut']) ?><br>
</p>

<h2 class="section-header">
    <span>Produits</span>

    <form method="GET" class="filters-inline">
        <input type="hidden" name="id" value="<?= $id ?>">

        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

        <select name="statut">
            <option value="">-- Statut --</option>
            <option value="en_attente" <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
            <option value="partielle" <?= ($_GET['statut'] ?? '') === 'partielle' ? 'selected' : '' ?>>Partielle</option>
            <option value="receptionnee" <?= ($_GET['statut'] ?? '') === 'receptionnee' ? 'selected' : '' ?>>Réceptionnée</option>
        </select>

        <button type="submit">OK</button>
    </form>
</h2>


<!-- ---------------------------------------------------------
     4. Tableau des produits
--------------------------------------------------------- -->
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
