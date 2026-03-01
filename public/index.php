<?php
require __DIR__ . '/inc/bootstrap.php';

$title = "Commandes à réceptionner";
require __DIR__ . '/inc/header.php';

/* ---------------------------------------------------------
   1. Récupération dynamique des statuts existants
--------------------------------------------------------- */
$statuts = $pdo->query("
    SELECT DISTINCT statut 
    FROM orders 
    ORDER BY statut ASC
")->fetchAll(PDO::FETCH_COLUMN);

/* ---------------------------------------------------------
   2. Normalisation des filtres GET
--------------------------------------------------------- */

// Recherche texte
$search = $_GET['q'] ?? '';

// Statut sélectionné (forcer en string)
$selected_status = $_GET['statut'] ?? '';
if (is_array($selected_status)) {
    $selected_status = reset($selected_status);
}

/* ---------------------------------------------------------
   3. Construction dynamique des filtres SQL
--------------------------------------------------------- */
$where = [];
$params = [];

// Recherche texte (numéro ou contact)
if ($search !== '') {
    $where[] = "(numero LIKE ? OR contact LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

/*
---------------------------------------------------------
 LOGIQUE DU FILTRE STATUT
---------------------------------------------------------
- Si aucun statut sélectionné → exclure "receptionnee"
- Si "receptionnee" sélectionné → afficher uniquement elle
- Sinon → filtrer sur le statut choisi
---------------------------------------------------------
*/

if ($selected_status === '') {
    // Aucun filtre → on exclut seulement "receptionnee"
    $where[] = "statut != 'receptionnee'";
}
elseif ($selected_status === 'receptionnee') {
    // Filtre explicite → on affiche uniquement les réceptionnées
    $where[] = "statut = 'receptionnee'";
}
else {
    // Filtre sur un autre statut
    $where[] = "statut = ?";
    $params[] = $selected_status;
}

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
	<span>
		Commandes à réceptionner
		<small style="font-size: 0.7em; opacity: 0.7;">
			(<?= count($orders) ?>)
		</small>
	</span>


    <form method="GET" class="filters-inline">
		<a href="index.php" class="reset-btn" title="Réinitialiser les filtres">
			<svg viewBox="0 0 24 24" class="reset-icon">
				<path fill="currentColor" d="M18 6L6 18M6 6l12 12"/>
			</svg>
		</a>

        <!-- Recherche texte -->
        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($search) ?>">

        <!-- LISTE DÉROULANTE SIMPLE (DYNAMIQUE) -->
        <select name="statut">
            <option value="">-- Tous les statuts --</option>

            <?php foreach ($statuts as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>"
                    <?= ($selected_status === $s) ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($s)) ?>
                </option>
            <?php endforeach; ?>
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
