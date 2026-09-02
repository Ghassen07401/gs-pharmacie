<h1><?= $medicament ? 'Modifier le medicament' : 'Ajouter un medicament' ?></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="index.php?c=medicament&a=<?= $medicament ? 'modifier&id=' . $medicament['id'] : 'ajouter' ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom du medicament *</label>
                <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($medicament['nom'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="categorie">Categorie *</label>
                <input type="text" id="categorie" name="categorie" required list="categories-list" value="<?= htmlspecialchars($medicament['categorie'] ?? '') ?>">
                <datalist id="categories-list">
                    <?php foreach ($categories as $cat): ?><option value="<?= htmlspecialchars($cat) ?>"><?php endforeach; ?>
                </datalist>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?= htmlspecialchars($medicament['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="fabricant">Fabricant</label>
                <input type="text" id="fabricant" name="fabricant" value="<?= htmlspecialchars($medicament['fabricant'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="prix">Prix (TND) *</label>
                <input type="number" step="0.01" min="0" id="prix" name="prix" required data-positive-number value="<?= htmlspecialchars($medicament['prix'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="stock">Stock actuel *</label>
                <input type="number" min="0" id="stock" name="stock" required data-positive-number value="<?= htmlspecialchars($medicament['stock'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label for="stock_minimum">Seuil de stock minimum *</label>
                <input type="number" min="0" id="stock_minimum" name="stock_minimum" required data-positive-number value="<?= htmlspecialchars($medicament['stock_minimum'] ?? '10') ?>">
            </div>
            <div class="form-group">
                <label for="date_expiration">Date d'expiration</label>
                <input type="date" id="date_expiration" name="date_expiration" value="<?= htmlspecialchars($medicament['date_expiration'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="necessite_ordonnance">Necessite une ordonnance ?</label>
            <select id="necessite_ordonnance" name="necessite_ordonnance">
                <option value="0" <?= (isset($medicament['necessite_ordonnance']) && !$medicament['necessite_ordonnance']) ? 'selected' : '' ?>>Non</option>
                <option value="1" <?= (isset($medicament['necessite_ordonnance']) && $medicament['necessite_ordonnance']) ? 'selected' : '' ?>>Oui</option>
            </select>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary"><?= $medicament ? 'Enregistrer les modifications' : 'Ajouter le medicament' ?></button>
            <a class="btn btn-outline" href="index.php?c=medicament&a=index">Annuler</a>
        </div>
    </form>
</div>
