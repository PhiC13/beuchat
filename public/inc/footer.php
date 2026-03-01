<?php
// --- Définition de la fonction footer (AVANT tout HTML) ---
function component_footer() {
    $timestamp = filemtime($_SERVER['SCRIPT_FILENAME']);
    $monthYear = date("F Y", $timestamp);

    $mois = [
        "January" => "Janvier",
        "February" => "Février",
        "March" => "Mars",
        "April" => "Avril",
        "May" => "Mai",
        "June" => "Juin",
        "July" => "Juillet",
        "August" => "Août",
        "September" => "Septembre",
        "October" => "Octobre",
        "November" => "Novembre",
        "December" => "Décembre"
    ];

    foreach ($mois as $en => $fr) {
        $monthYear = str_replace($en, $fr, $monthYear);
    }
    ?>

    <footer class="lbj-footer">
        ©︎ <?= $monthYear ?> – Réception Beuchat – Philippe (PhiC13) – Le Bateau Jaune
    </footer>

    <?php
}
?>

</div> <!-- .container -->

<!-- SCRIPT DU MENU HAMBURGER -->
<script>
document.getElementById('hamburgerToggle').addEventListener('click', () => {
    document.body.classList.toggle('menu-open');
});

document.querySelectorAll('.menu-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.parentElement.classList.toggle('open');
    });
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('.hamburger-container')) {
        document.body.classList.remove('menu-open');
    }
});
</script>


</body>
</html>
