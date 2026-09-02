<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creer un compte - <?= APP_NAME ?></title>
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
        <h1>Creer un compte</h1>
        <p class="subtitle">Espace client - consultez et commandez vos medicaments</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?c=auth&a=register" data-validate novalidate>
            <?= Csrf::field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prenom</label>
                    <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($old['prenom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="telephone">Telephone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($old['adresse'] ?? '') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required data-min-length="6">
                </div>
                <div class="form-group">
                    <label for="confirmation">Confirmer le mot de passe</label>
                    <input type="password" id="confirmation" name="confirmation" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Creer mon compte</button>
        </form>

        <div class="switch-link">
            Deja inscrit ? <a href="index.php?c=auth&a=login">Se connecter</a>
        </div>
    </div>
    <script src="assets/js/validation.js"></script>
</body>
</html>
