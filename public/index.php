<?php
require __DIR__ . '/inc/bootstrap.php';

$title = "Commandes à réceptionner";
require __DIR__ . '/inc/header.php';

/* ---------------------------------------------------------
   1. Construction dynamique des filtres
--------------------------------------------------------- */
$where = [];
$params = [];

// Recherche texte (numéro ou contact)
if (!empty($_GET['q'])) {
    $where[] = "(numero LIKE ? OR contact LIKE ?)";
    $params[] = "%" . $_GET['q'] . "%";
    $params[] = "%" . $_GET['q'] . "%";
}

// Filtre statut
if (!empty($_GET['statut'])) {
    $where[] = "statut = ?";
    $params[] = $_GET['statut'];
}

// Par défaut : exclure les commandes totalement réceptionnées
$where[] = "statut != 'receptionnee'";

$sql = "
    SELECT id, numero, contact, date_commande, statut, facture, created_at
    FROM orders
    " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
    ORDER BY date_commande DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

log_event($pdo, 'list_orders', 'Affichage de la liste des commandes', [
    'filters' => $_GET
]);
?>

<h1 class="section-header">
    <span>Commandes à réceptionner</span>

    <form method="GET" class="filters-inline">
        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

        <select name="statut">
            <option value="">-- Statut --</option>
            <option value="en attente" <?= ($_GET['statut'] ?? '') === 'en attente' ? 'selected' : '' ?>>En attente</option>
            <option value="partielle" <?= ($_GET['statut'] ?? '') === 'partielle' ? 'selected' : '' ?>>Partielle</option>
            <option value="receptionnee" <?= ($_GET['statut'] ?? '') === 'receptionnee' ? 'selected' : '' ?>>Réceptionnée</option>
        </select>

        <button type="submit">OK</button>
    </form>
</h1>

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Contact</th>
            <th>Date</th>
            <th>Statut</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= htmlspecialchars($o['numero']) ?></td>
            <td><?= htmlspecialchars($o['contact']) ?></td>
            <td><?= htmlspecialchars($o['date_commande']) ?></td>
            <td><?= htmlspecialchars($o['statut']) ?></td>
            <td><a class="button" href="commande.php?id=<?= $o['id'] ?>">Voir</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/inc/footer.php'; ?>
