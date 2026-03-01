<?php
require __DIR__ . '/inc/bootstrap.php';

$title = "Logs – Réceptions";
require __DIR__ . '/inc/header.php';

/* ---------------------------------------------------------
   1. Construction des filtres
--------------------------------------------------------- */
$where = [];
$params = [];

// Recherche utilisateur
$search = $_GET['q'] ?? '';
if ($search !== '') {
    $where[] = "utilisateur LIKE ?";
    $params[] = "%" . $search . "%";
}

$sql = "
    SELECT r.*, oi.reference, oi.nom
    FROM receptions r
    LEFT JOIN order_items oi ON oi.id = r.order_item_id
    " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
    ORDER BY date_reception DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<h1 class="section-header">
    <span>
        Logs réceptions
        <small style="font-size: 0.7em; opacity: 0.7;">
            (<?= count($logs) ?>)
        </small>
    </span>

    <form method="GET" class="filters-inline">

        <!-- Reset -->
        <a href="logs_receptions.php" class="reset-btn" title="Réinitialiser les filtres">
            <svg viewBox="0 0 24 24" class="reset-icon">
                <path fill="currentColor" d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </a>

        <!-- Recherche utilisateur -->
        <input type="text" name="q" placeholder="Utilisateur..."
               value="<?= htmlspecialchars($search) ?>">

        <button type="submit">OK</button>
    </form>
</h1>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Réf</th>
            <th>Nom</th>
            <th>Quantité</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['date_reception']) ?></td>
            <td><?= htmlspecialchars($log['utilisateur']) ?></td>
            <td><?= htmlspecialchars($log['reference']) ?></td>
            <td><?= htmlspecialchars($log['nom']) ?></td>
            <td><?= htmlspecialchars($log['quantite']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/inc/footer.php'; ?>
<?php component_footer(); ?>
