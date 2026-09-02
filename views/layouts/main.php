<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        /* Applique le theme memorise AVANT le premier rendu : sans cela,
           une page sombre s'afficherait une fraction de seconde en clair. */
        (function () {
            try {
                var choix = localStorage.getItem('pharmacie-theme');
                if (choix === 'dark' || choix === 'light') {
                    document.documentElement.setAttribute('data-theme', choix);
                }
            } catch (e) {
                /* stockage indisponible : on garde la preference du systeme */
            }
        })();
    </script>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand">💊 Pharmacie</div>
        <nav>
            <a href="index.php?c=dashboard&a=index">Tableau de bord</a>

            <?php if (Auth::role() === 'responsable'): ?>
                <a href="index.php?c=medicament&a=index">Gestion des medicaments</a>
                <a href="index.php?c=medicament&a=stockCritique">Rapport stock critique</a>
                <a href="index.php?c=medicament&a=peremptions">Rapport peremptions</a>
                <a href="index.php?c=transaction&a=rapportExpeditions">Rapport expeditions</a>
                <a href="index.php?c=ordonnance&a=index">Ordonnances</a>
                <a href="index.php?c=utilisateur&a=index">Gestion des utilisateurs</a>
                <a href="index.php?c=interaction&a=index">Interactions medicamenteuses</a>
            <?php elseif (Auth::role() === 'pharmacien'): ?>
                <a href="index.php?c=ordonnance&a=index">Ordonnances a valider</a>
                <a href="index.php?c=interaction&a=index">Interactions medicamenteuses</a>
                <a href="index.php?c=transaction&a=mesTransactions">Mes transactions</a>
                <a href="index.php?c=medicament&a=catalogue">Catalogue medicaments</a>
            <?php elseif (Auth::role() === 'client'): ?>
                <a href="index.php?c=medicament&a=catalogue">Medicaments disponibles</a>
                <a href="index.php?c=ordonnance&a=soumettre">Soumettre une ordonnance</a>
                <a href="index.php?c=ordonnance&a=historique">Historique ordonnances</a>
                <a href="index.php?c=transaction&a=historiqueAchats">Historique achats</a>
            <?php endif; ?>
        </nav>
        <div class="user-box">
            Connecte : <strong><?= htmlspecialchars(Auth::fullName()) ?></strong><br>
            Role : <?= htmlspecialchars(ucfirst(Auth::role())) ?><br>
            <a href="index.php?c=auth&a=logout" style="color:#fff;text-decoration:underline;">Se deconnecter</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <button class="menu-toggle" type="button">☰ Menu</button>
            <button class="theme-toggle no-print" type="button" data-theme-toggle
                aria-label="Basculer entre le mode clair et le mode sombre">
            <span class="theme-icon" aria-hidden="true">🌙</span>
            <span class="theme-label">Mode sombre</span>
        </button>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="print-header">
            <div class="print-brand">
                <?= APP_NAME ?>
                <span>Document interne</span>
            </div>
            <div class="print-meta">
                Edite le <strong><?= date('d/m/Y') ?></strong> a <strong><?= date('H:i') ?></strong><br>
                Par <strong><?= htmlspecialchars(Auth::fullName()) ?></strong>
                (<?= htmlspecialchars(ucfirst(Auth::role())) ?>)
            </div>
        </div>

        <?= $content ?>

        <div class="print-footer">
            <?= APP_NAME ?> &middot; ESPRIT &mdash; Projet Technologies Web 2A &middot; Annee universitaire 2025-2026
        </div>
    </main>
</div>
<script src="assets/js/validation.js"></script>
</body>
</html>
