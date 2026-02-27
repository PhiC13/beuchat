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
    <button class="hamburger-btn" onclick="document.body.classList.toggle('menu-open');">
        ☰
    </button>

    <nav class="hamburger-menu">
        <a href="index.php">Accueil</a>
        <a href="export_commandes.php">Export commandes</a>
        <a href="export_produits.php">Export produits</a>
        <a href="export_receptions.php">Export réceptions</a>
    </nav>
</div>

<div class="container">
