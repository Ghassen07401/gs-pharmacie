<h1>Soumettre une ordonnance</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if (!empty($interactions)): ?>
    <div class="interaction-alert">
        <h3>⚠ Interactions medicamenteuses potentielles detectees</h3>
        <ul>
            <?php foreach ($interactions as $i): ?>
                <li>
                    <strong><?= htmlspecialchars($i['med1_nom']) ?> + <?= htmlspecialchars($i['med2_nom']) ?></strong>
                    (<?= htmlspecialchars($i['niveau_gravite']) ?>) : <?= htmlspecialchars($i['description']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p style="margin:8px 0 0;font-size:0.85rem;">Votre ordonnance a tout de meme ete corrigee ci-dessous si necessaire ; le pharmacien sera aussi alerte lors de la validation.</p>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" action="index.php?c=ordonnance&a=soumettre" data-validate novalidate>
        <?= Csrf::field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="medecin_nom">Nom du medecin prescripteur *</label>
                <input type="text" id="medecin_nom" name="medecin_nom" required>
            </div>
            <div class="form-group">
                <label for="date_prescription">Date de prescription *</label>
                <input type="date" id="date_prescription" name="date_prescription" required max="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label for="type">Type de demande</label>
                <select id="type" name="type">
                    <option value="nouvelle">Nouvelle ordonnance</option>
                    <option value="renouvellement">Demande de renouvellement</option>
                </select>
            </div>
        </div>

        <h3>Medicaments prescrits</h3>
        <div id="items-container">
            <div class="item-row form-row" style="align-items:flex-end;">
                <div class="form-group">
                    <label>Medicament</label>
                    <select name="medicament_id[]" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($medicaments as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?> (stock: <?= (int) $m['stock'] ?>)<?= $m['necessite_ordonnance'] ? ' - ordonnance requise' : '' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="max-width:120px;">
                    <label>Quantite</label>
                    <input type="number" name="quantite[]" min="1" value="1" required data-positive-number>
                </div>
                <div class="form-group">
                    <label>Posologie</label>
                    <input type="text" name="posologie[]" placeholder="ex : 1 comprime matin et soir">
                </div>
                <div class="form-group" style="max-width:100px;">
                    <button type="button" class="btn btn-outline btn-sm remove-item-btn">Retirer</button>
                </div>
            </div>
        </div>

        <template id="item-template">
            <div class="item-row form-row" style="align-items:flex-end;">
                <div class="form-group">
                    <label>Medicament</label>
                    <select name="medicament_id[]" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($medicaments as $m): ?>
                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?> (stock: <?= (int) $m['stock'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="max-width:120px;">
                    <label>Quantite</label>
                    <input type="number" name="quantite[]" min="1" value="1" required data-positive-number>
                </div>
                <div class="form-group">
                    <label>Posologie</label>
                    <input type="text" name="posologie[]">
                </div>
                <div class="form-group" style="max-width:100px;">
                    <button type="button" class="btn btn-outline btn-sm remove-item-btn">Retirer</button>
                </div>
            </div>
        </template>

        <button type="button" id="add-item-btn" class="btn btn-outline btn-sm">+ Ajouter un medicament</button>

        <div class="actions" style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">Soumettre l'ordonnance</button>
            <a class="btn btn-outline" href="index.php?c=dashboard&a=index">Annuler</a>
        </div>
    </form>
</div>
