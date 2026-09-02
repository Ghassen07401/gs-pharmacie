<h1>Valider l'ordonnance #<?= $ordonnance['id'] ?></h1>

<?php if (!empty($perimes)): ?>
    <div class="alert alert-error">
        <strong>Medicament perime — la delivrance est interdite :</strong>
        <ul>
            <?php foreach ($perimes as $p): ?>
                <li><?= htmlspecialchars($p) ?></li>
            <?php endforeach; ?>
        </ul>
        Retirez ces produits du stock, puis refusez cette ordonnance ou attendez un reapprovisionnement.
    </div>
<?php endif; ?>

<?php if (!empty($rupture)): ?>
    <div class="alert alert-error">
        <strong>Stock insuffisant — la validation est impossible :</strong>
        <ul>
            <?php foreach ($rupture as $r): ?>
                <li><?= htmlspecialchars($r) ?></li>
            <?php endforeach; ?>
        </ul>
        Reapprovisionnez le stock avant de valider cette ordonnance.
    </div>
<?php endif; ?>

<?php if (!empty($interactions)): ?>
    <div class="interaction-alert">
        <h3>⚠ Interactions medicamenteuses detectees dans cette ordonnance</h3>
        <ul>
            <?php foreach ($interactions as $i): ?>
                <li>
                    <strong><?= htmlspecialchars($i['med1_nom']) ?> + <?= htmlspecialchars($i['med2_nom']) ?></strong>
                    (<?= htmlspecialchars($i['niveau_gravite']) ?>) : <?= htmlspecialchars($i['description']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <p style="margin:8px 0 0;font-size:0.85rem;">
            Verifiez ces associations avant de valider ; vous pouvez refuser l'ordonnance et en indiquer le motif au client.
        </p>
    </div>
<?php endif; ?>

<div class="card">
    <p><strong>Client :</strong> <?= htmlspecialchars($ordonnance['client_nom']) ?></p>
    <p><strong>Medecin :</strong> <?= htmlspecialchars($ordonnance['medecin_nom']) ?></p>

    <h3>Medicaments</h3>
    <ul>
        <?php foreach ($items as $it): ?>
            <li>
                <?= htmlspecialchars($it['medicament_nom']) ?> — quantite : <?= (int) $it['quantite'] ?>
                — stock disponible : <?= (int) $it['stock'] ?>
                <?php if (Medicament::etatPeremption($it['date_expiration'] ?? null) === 'perime'): ?>
                    <span class="badge badge-refusee">perime le <?= htmlspecialchars($it['date_expiration']) ?></span>
                <?php endif; ?>
                <?= htmlspecialchars($it['posologie'] ?? '') !== '' ? ' — ' . htmlspecialchars($it['posologie']) : '' ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <p style="color:var(--color-text-muted);font-size:0.9rem;">
        La validation genere automatiquement l'expedition correspondante et deduit les quantites du stock.
    </p>

    <form method="POST" action="index.php?c=ordonnance&a=valider&id=<?= $ordonnance['id'] ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <div class="form-group">
            <label for="commentaire">Commentaire (optionnel)</label>
            <textarea id="commentaire" name="commentaire" rows="3" placeholder="Recommandations pour le client..."></textarea>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-success" <?= (!empty($rupture) || !empty($perimes)) ? 'disabled' : '' ?>>
                Confirmer la validation
            </button>
            <a class="btn btn-outline" href="index.php?c=ordonnance&a=voir&id=<?= $ordonnance['id'] ?>">Annuler</a>
        </div>
    </form>
</div>
