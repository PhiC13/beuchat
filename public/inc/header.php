<?php require_once __DIR__ . '/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? "Réception des Commandes Beuchat" ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/menu.css">
</head>
<body>

<div class="hamburger-container">

    <div class="top-bar">

        <div class="top-bar-left">
            <img src="assets/LogoLBJ_Small.png" alt="Logo Le Bateau Jaune" class="lbj-logo">
            <h1>Réception Beuchat</h1>
        </div>

        <div class="top-bar-right">

            <div class="top-switch">
                <a href="index.php"
                   class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                    Commandes
                </a>

                <a href="produits.php"
                   class="<?= basename($_SERVER['PHP_SELF']) === 'produits.php' ? 'active' : '' ?>">
                    Produits
                </a>
            </div>

            <a href="update.php" class="refresh-btn" id="refreshTrigger" title="Mettre à jour la base">
                <svg class="refresh-icon" viewBox="0 0 24 24">
                    <path fill="currentColor"
                          d="M17.65 6.35A8 8 0 1 0 19 12h-2a6 6 0 1 1-1.76-4.24L13 10h7V3l-2.35 3.35z"/>
                </svg>
            </a>

            <div id="refreshSpinner" class="spinner" style="display:none;"></div>

            <button class="hamburger-btn" id="hamburgerToggle">
                ☰
            </button>
        </div>

    </div>

    <!-- ICI seulement le contenu du menu, y compris Logs -->
    <nav class="hamburger-menu">

        <div class="menu-group">
            <button class="menu-toggle">Exports ▾</button>
            <div class="menu-sub">
                <a href="export_commandes.php">Export commandes</a>
                <a href="export_produits.php">Export produits</a>
                <a href="export_receptions.php">Export réceptions</a>
            </div>
        </div>

        <div class="menu-group">
            <button class="menu-toggle">Logs ▾</button>
            <div class="menu-sub">
                <a href="logs_updates.php">Mises à jour base</a>
                <a href="logs_actions.php">Actions utilisateur</a>
                <a href="logs_receptions.php">Réceptions</a>
            </div>
        </div>

    </nav>

</div>


<div class="container">

<?php if (!empty($_GET['updated'])): ?>
    <div class="alert-success" id="alertMessage">
        ✔ Base mise à jour avec succès
    </div>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div class="alert-error" id="alertMessage">
        ⚠ Erreur lors de la mise à jour : <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<!-- SCRIPT SPINNER + AUTO-DISMISS -->
<script>
// Affichage du spinner au clic
document.getElementById('refreshTrigger')?.addEventListener('click', function(e) {
    document.getElementById('refreshSpinner').style.display = 'inline-block';
    this.style.display = 'none';
});

// Disparition automatique des messages
setTimeout(function() {
    const alert = document.getElementById('alertMessage');
    if (alert) {
        alert.style.transition = "opacity 0.5s";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    }
}, 3500);
</script>
