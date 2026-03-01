<?php
require __DIR__ . '/inc/bootstrap.php';

$title = "Logs – Mise à jour de la base";
require __DIR__ . '/inc/header.php';

/* ---------------------------------------------------------
   1. Récupération des statuts distincts
--------------------------------------------------------- */
$statuts = $pdo->query("
    SELECT DISTINCT status
    FROM scrape_logs
    ORDER BY status ASC
")->fetchAll(PDO::FETCH_COLUMN);

/* ---------------------------------------------------------
   2. Construction dynamique des filtres
--------------------------------------------------------- */
$where = [];
$params = [];

// Recherche texte
$search = $_GET['q'] ?? '';
if ($search !== '') {
    $where[] = "message LIKE ?";
    $params[] = "%" . $search . "%";
}

// Filtre statut
$selected_status = $_GET['status'] ?? '';
if ($selected_status !== '') {
    $where[] = "status = ?";
    $params[] = $selected_status;
}

$sql = "
    SELECT *
    FROM scrape_logs
    " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
    ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<h1 class="section-header">
    <span>
        Logs mise à jour
        <small style="font-size: 0.7em; opacity: 0.7;">
            (<?= count($logs) ?>)
        </small>
    </span>

    <form method="GET" class="filters-inline">

        <!-- Reset -->
        <a href="logs_updates.php" class="reset-btn" title="Réinitialiser les filtres">
            <svg viewBox="0 0 24 24" class="reset-icon">
                <path fill="currentColor" d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </a>

        <!-- Recherche -->
        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($search) ?>">

        <!-- Statut -->
        <select name="status">
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
            <th>Date</th>
            <th>Statut</th>
            <th>Message</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['created_at']) ?></td>
            <td><?= htmlspecialchars($log['status']) ?></td>
            <td><pre style="white-space:pre-wrap;"><?= htmlspecialchars($log['message']) ?></pre></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/inc/footer.php'; ?>
<?php component_footer(); ?>
