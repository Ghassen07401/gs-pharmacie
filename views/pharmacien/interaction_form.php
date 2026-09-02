<h1>Enregistrer une interaction medicamenteuse</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="index.php?c=interaction&a=ajouter" data-validate novalidate>
        <?= Csrf::field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="medicament_1_id">Medicament 1 *</label>
                <select id="medicament_1_id" name="medicament_1_id" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($medicaments as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="medicament_2_id">Medicament 2 *</label>
                <select id="medicament_2_id" name="medicament_2_id" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($medicaments as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="niveau_gravite">Niveau de gravite *</label>
            <select id="niveau_gravite" name="niveau_gravite" required>
                <option value="faible">Faible</option>
                <option value="moderee" selected>Moderee</option>
                <option value="grave">Grave</option>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="3" required placeholder="Decrivez le risque encouru en cas d'association..."></textarea>
        </div>

        <div class="actions">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-outline" href="index.php?c=interaction&a=index">Annuler</a>
        </div>
    </form>
</div>
