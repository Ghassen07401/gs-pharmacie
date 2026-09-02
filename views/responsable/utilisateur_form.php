<?php $isEdit = isset($utilisateur['id']); ?>
<h1><?= $isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' ?></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="index.php?c=utilisateur&a=<?= $isEdit ? 'modifier&id=' . $utilisateur['id'] : 'ajouter' ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="prenom">Prenom *</label>
                <input type="text" id="prenom" name="prenom" required value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    <?php foreach (['responsable' => 'Responsable', 'pharmacien' => 'Pharmacien', 'client' => 'Client'] as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($utilisateur['role'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="telephone">Telephone</label>
                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="adresse">Adresse</label>
                <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($utilisateur['adresse'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="mot_de_passe"><?= $isEdit ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' ?></label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" <?= $isEdit ? '' : 'required' ?> data-min-length="6">
        </div>

        <?php if ($isEdit): ?>
            <div class="form-group">
                <label for="actif">Statut du compte</label>
                <select id="actif" name="actif">
                    <option value="1" <?= ($utilisateur['actif'] ?? 1) ? 'selected' : '' ?>>Actif</option>
                    <option value="0" <?= !($utilisateur['actif'] ?? 1) ? 'selected' : '' ?>>Desactive</option>
                </select>
            </div>
        <?php endif; ?>

        <div class="actions">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Enregistrer' : 'Creer le compte' ?></button>
            <a class="btn btn-outline" href="index.php?c=utilisateur&a=index">Annuler</a>
        </div>
    </form>
</div>
