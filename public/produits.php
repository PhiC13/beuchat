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
$search = $_GET['q'] ?? '';
if ($search !== '') {
    $where[] = "(reference LIKE ? OR nom LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

// Filtre simple : réceptionné / non réceptionné
$filtre_statut = $_GET['statut'] ?? '';

$having = [];

/*
   Agrégats normalisés :

   total_cmd   = SUM(COALESCE(CAST(quantite_commandee AS UNSIGNED), 0))
   total_recue = SUM(COALESCE(CAST(quantite_recue     AS UNSIGNED), 0))

   - "Réceptionnés"      : total_cmd > 0 AND total_recue >= total_cmd
   - "Non réceptionnés"  : total_cmd > 0 AND total_recue <  total_cmd
*/

if ($filtre_statut === 'receptionne') {
    $having[] = "SUM(COALESCE(CAST(quantite_commandee AS UNSIGNED), 0)) > 0";
    $having[] = "SUM(COALESCE(CAST(quantite_recue     AS UNSIGNED), 0)) >= 
                 SUM(COALESCE(CAST(quantite_commandee AS UNSIGNED), 0))";
} elseif ($filtre_statut === 'non_receptionne') {
    $having[] = "SUM(COALESCE(CAST(quantite_commandee AS UNSIGNED), 0)) > 0";
    $having[] = "SUM(COALESCE(CAST(quantite_recue     AS UNSIGNED), 0)) < 
                 SUM(COALESCE(CAST(quantite_commandee AS UNSIGNED), 0))";
}

$sql = "
    SELECT 
        reference,
        nom,
        SUM(COALESCE(CAST(quantite_commandee AS UNSIGNED), 0)) AS total_cmd,
        SUM(COALESCE(CAST(quantite_recue     AS UNSIGNED), 0)) AS total_recue,
        COUNT(DISTINCT order_id) AS nb_commandes
    FROM order_items
    " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
    GROUP BY reference, nom
    " . (count($having) ? "HAVING " . implode(" AND ", $having) : "") . "
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
    <span>
        Produits 
        <small style="font-size: 0.7em; opacity: 0.7;">
            (<?= count($produits) ?>)
        </small>
    </span>

    <form method="GET" class="filters-inline">

        <!-- Bouton reset -->
		<a href="produits.php" class="reset-btn" title="Réinitialiser les filtres">
			<svg viewBox="0 0 24 24" class="reset-icon">
				<path fill="currentColor" d="M18 6L6 18M6 6l12 12"/>
			</svg>
		</a>

        <!-- Recherche texte -->
        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($search) ?>">

        <!-- Filtre statut -->
        <select name="statut">
            <option value="">-- Tous les produits --</option>
            <option value="non_receptionne" <?= $filtre_statut === 'non_receptionne' ? 'selected' : '' ?>>
                Non réceptionnés
            </option>
            <option value="receptionne" <?= $filtre_statut === 'receptionne' ? 'selected' : '' ?>>
                Réceptionnés
            </option>
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
