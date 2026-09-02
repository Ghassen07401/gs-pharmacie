<h1>Ordonnance #<?= $ordonnance['id'] ?></h1>

<div class="actions no-print" style="margin-bottom:14px;">
    <button type="button" class="btn btn-outline btn-sm" data-print>Imprimer / PDF</button>
</div>

<div class="card">
    <p><strong>Medecin :</strong> <?= htmlspecialchars($ordonnance['medecin_nom']) ?></p>
    <p><strong>Date de prescription :</strong> <?= htmlspecialchars($ordonnance['date_prescription']) ?></p>
    <p><strong>Type :</strong> <?= htmlspecialchars(ucfirst($ordonnance['type'])) ?></p>
    <p><strong>Statut :</strong>
        <span class="badge badge-<?= $ordonnance['statut'] === 'en_attente' ? 'attente' : $ordonnance['statut'] ?>">
            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $ordonnance['statut']))) ?>
        </span>
    </p>
    <?php if ($ordonnance['valideur_nom']): ?>
        <p><strong>Traitee par :</strong> <?= htmlspecialchars($ordonnance['valideur_nom']) ?> le <?= htmlspecialchars($ordonnance['date_validation']) ?></p>
    <?php endif; ?>
    <?php if (!empty($ordonnance['commentaire'])): ?>
        <p><strong>Commentaire du pharmacien :</strong> <?= htmlspecialchars($ordonnance['commentaire']) ?></p>
    <?php endif; ?>
</div>

<?php if (!empty($interactions)): ?>
    <div class="interaction-alert">
        <h3>⚠ Interactions medicamenteuses potentielles</h3>
        <ul>
            <?php foreach ($interactions as $i): ?>
                <li><strong><?= htmlspecialchars($i['med1_nom']) ?> + <?= htmlspecialchars($i['med2_nom']) ?></strong> (<?= htmlspecialchars($i['niveau_gravite']) ?>) : <?= htmlspecialchars($i['description']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <h2>Medicaments prescrits</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Medicament</th><th>Quantite</th><th>Posologie</th><th>Prix unitaire</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?= htmlspecialchars($it['medicament_nom']) ?></td>
                <td><?= (int) $it['quantite'] ?></td>
                <td><?= htmlspecialchars($it['posologie'] ?? '-') ?></td>
                <td><?= number_format((float) $it['prix'], 2) ?> TND</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<a class="btn btn-outline" href="index.php?c=ordonnance&a=historique">← Retour a mon historique</a>
