<?php
require __DIR__ . '/inc/bootstrap.php';

$title = "Vue Produits";
require __DIR__ . '/inc/header.php';

/* ---------------------------------------------------------
   1. Construction dynamique des filtres
--------------------------------------------------------- */
$where = [];
$params = [];

// Recherche texte
if (!empty($_GET['q'])) {
    $where[] = "(reference LIKE ? OR nom LIKE ?)";
    $params[] = "%" . $_GET['q'] . "%";
    $params[] = "%" . $_GET['q'] . "%";
}

// Filtre statut global
if (!empty($_GET['statut'])) {
    if ($_GET['statut'] === 'en_attente') {
        $where[] = "SUM(CAST(quantite_recue AS UNSIGNED)) = 0";
    } elseif ($_GET['statut'] === 'partielle') {
        $where[] = "SUM(CAST(quantite_recue AS UNSIGNED)) > 0 
                    AND SUM(CAST(quantite_recue AS UNSIGNED)) < SUM(CAST(quantite_commandee AS UNSIGNED))";
    } elseif ($_GET['statut'] === 'receptionnee') {
        $where[] = "SUM(CAST(quantite_recue AS UNSIGNED)) = SUM(CAST(quantite_commandee AS UNSIGNED))";
    }
}

$sql = "
    SELECT 
        reference,
        nom,
        SUM(CAST(quantite_commandee AS UNSIGNED)) AS total_cmd,
        SUM(CAST(quantite_recue AS UNSIGNED)) AS total_recue,
        COUNT(DISTINCT order_id) AS nb_commandes
    FROM order_items
    " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
    GROUP BY reference, nom
    ORDER BY reference
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

log_event($pdo, 'view_products_list', 'Consultation de la liste des produits', [
    'filters' => $_GET
]);
?>

<h1 class="section-header">
    <span>Produits</span>

    <form method="GET" class="filters-inline">
        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

        <select name="statut">
            <option value="">-- Statut --</option>
            <option value="en_attente" <?= ($_GET['statut'] ?? '') === 'en_attente' ? 'selected' : '' ?>>En attente</option>
            <option value="partielle" <?= ($_GET['statut'] ?? '') === 'partielle' ? 'selected' : '' ?>>Partielle</option>
            <option value="receptionnee" <?= ($_GET['statut'] ?? '') === 'receptionnee' ? 'selected' : '' ?>>Réceptionné</option>
        </select>

        <button type="submit">OK</button>
    </form>
</h1>

<table>
    <thead>
        <tr>
            <th>Réf</th>
            <th>Nom</th>
            <th>Total commandé</th>
            <th>Total reçu</th>
            <th>Commandes</th>
            <th></th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($produits as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['reference']) ?></td>
            <td><?= htmlspecialchars($p['nom']) ?></td>
            <td><?= intval($p['total_cmd']) ?></td>
            <td><?= intval($p['total_recue']) ?></td>
            <td><?= intval($p['nb_commandes']) ?></td>
            <td><a class="button" href="produit.php?ref=<?= urlencode($p['reference']) ?>">Détails</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/inc/footer.php'; ?>
