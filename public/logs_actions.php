<?php
require __DIR__ . '/inc/bootstrap.php';

$title = "Logs – Activité";
require __DIR__ . '/inc/header.php';

/* ---------------------------------------------------------
   1. Récupération des types distincts
--------------------------------------------------------- */
$types = $pdo->query("
    SELECT DISTINCT type
    FROM logs
    ORDER BY type ASC
")->fetchAll(PDO::FETCH_COLUMN);

/* ---------------------------------------------------------
   2. Construction des filtres
--------------------------------------------------------- */
$where = [];
$params = [];

// Recherche texte
$search = $_GET['q'] ?? '';
if ($search !== '') {
    $where[] = "message LIKE ?";
    $params[] = "%" . $search . "%";
}

// Filtre type
$selected_type = $_GET['type'] ?? '';
if ($selected_type !== '') {
    $where[] = "type = ?";
    $params[] = $selected_type;
}

$sql = "
    SELECT *
    FROM logs
    " . (count($where) ? "WHERE " . implode(" AND ", $where) : "") . "
    ORDER BY created_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<h1 class="section-header">
    <span>
        Logs activité
        <small style="font-size: 0.7em; opacity: 0.7;">
            (<?= count($logs) ?>)
        </small>
    </span>

    <form method="GET" class="filters-inline">

        <!-- Reset -->
        <a href="logs_actions.php" class="reset-btn" title="Réinitialiser les filtres">
            <svg viewBox="0 0 24 24" class="reset-icon">
                <path fill="currentColor" d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </a>

        <!-- Recherche -->
        <input type="text" name="q" placeholder="Recherche..."
               value="<?= htmlspecialchars($search) ?>">

        <!-- Type -->
        <select name="type">
            <option value="">-- Tous les types --</option>
            <?php foreach ($types as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>"
                    <?= ($selected_type === $t) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t) ?>
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
            <th>Type</th>
            <th>Message</th>
            <th>Contexte</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['created_at']) ?></td>
            <td><?= htmlspecialchars($log['type']) ?></td>
            <td><?= htmlspecialchars($log['message']) ?></td>
            <td>
                <pre style="white-space:pre-wrap;">
<?= json_encode(json_decode($log['context'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
                </pre>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require __DIR__ . '/inc/footer.php'; ?>
<?php component_footer(); ?>
