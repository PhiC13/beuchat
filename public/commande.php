<?php
require __DIR__ . '/inc/bootstrap.php';

$id = intval($_GET['id'] ?? 0);

/* ---------------------------------------------------------
   0. Récupération de la commande
--------------------------------------------------------- */
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
   1. Récupération dynamique des statuts des items
--------------------------------------------------------- */
$statuts_items = $pdo->query("
    SELECT DISTINCT statut
    FROM order_items
    WHERE order_id = $id
    ORDER BY statut ASC
")->fetchAll(PDO::FETCH_COLUMN);

/* ---------------------------------------------------------
   2. Construction dynamique des filtres
--------------------------------------------------------- */
$where = ["order_id = ?"];
$params = [$id];

// Recherche texte (référence ou nom)
$search = $_GET['q'] ?? '';
if ($search !== '') {
    $where[] = "(reference LIKE ? OR nom LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

// Filtre statut
$selected_status = $_GET['statut'] ?? '';
if ($selected_status !== '') {
    $where[] = "statut = ?";
    $params[] = $selected_status;
}

/* ---------------------------------------------------------
   3. Requête SQL dynamique
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

/* ---------------------------------------------------------
   4. Header
--------------------------------------------------------- */
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
    <span>
        Produits
        <small style="font-size: 0.7em; opacity: 0.7;">
            (<?= count($items) ?>)
        </small>
    </span>

    <form method="GET" class="filters-inline">
        <input type="hidden" name="id" value="<?= $id ?>">

        <!-- Bouton reset -->
        <a href="commande.php?id=<?= $id ?>" class="reset-btn" title="Réinitialiser les filtres">
            <svg viewBox="0 0 24 24" class="reset-icon">
                <path fill="currentColor" d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </a>

        <!-- Recherche texte -->
        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($search) ?>">

        <!-- Liste déroulante dynamique -->
        <select name="statut">
            <option value="">-- Tous les statuts --</option>

            <?php foreach ($statuts_items as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>"
                    <?= ($selected_status === $s) ? 'selected' : '' ?>>
                    <?= ucfirst(htmlspecialchars($s)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">OK</button>
    </form>
</h2>

<!-- ---------------------------------------------------------
     5. Tableau des produits
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
<?php component_footer(); ?>
