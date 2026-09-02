<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= APP_NAME ?></title>
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
<body class="public-page">
        <button class="theme-toggle no-print" type="button" data-theme-toggle
                aria-label="Basculer entre le mode clair et le mode sombre">
            <span class="theme-icon" aria-hidden="true">🌙</span>
            <span class="theme-label">Mode sombre</span>
        </button>
    <div class="auth-card">
        <h1>💊 Pharmacie</h1>
        <p class="subtitle">Connectez-vous a votre espace</p>

        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>">
                <?= htmlspecialchars($_SESSION['flash']['message']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?c=auth&a=login" data-validate novalidate>
            <?= Csrf::field() ?>
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required placeholder="vous@exemple.com">
            </div>
            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="********">
            </div>
            <button type="submit" class="btn btn-primary">Se connecter</button>
        </form>

        <div class="switch-link">
            Pas encore de compte ? <a href="index.php?c=auth&a=register">Creer un compte client</a>
        </div>

        <p style="margin-top:18px;font-size:0.78rem;color:#8a9391;">
            Comptes de demonstration (mot de passe : <code>password123</code>) :<br>
            responsable@pharmacie.tn · pharmacien@pharmacie.tn · client@pharmacie.tn
        </p>
    </div>
    <script src="assets/js/validation.js"></script>
</body>
</html>
