<h1>Ordonnance #<?= $ordonnance['id'] ?></h1>

<div class="actions no-print" style="margin-bottom:14px;">
    <button type="button" class="btn btn-outline btn-sm" data-print>Imprimer / PDF</button>
</div>

<div class="card">
    <p><strong>Client :</strong> <?= htmlspecialchars($ordonnance['client_nom']) ?> (<?= htmlspecialchars($ordonnance['client_email']) ?>)</p>
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
        <thead><tr><th>Medicament</th><th>Quantite</th><th>Posologie</th><th>Prix unitaire</th><th>Total</th></tr></thead>
        <tbody>
        <?php $total = 0; foreach ($items as $it): $total += $it['prix'] * $it['quantite']; ?>
            <tr>
                <td><?= htmlspecialchars($it['medicament_nom']) ?></td>
                <td><?= (int) $it['quantite'] ?></td>
                <td><?= htmlspecialchars($it['posologie'] ?? '-') ?></td>
                <td><?= number_format((float) $it['prix'], 2) ?> TND</td>
                <td><?= number_format($it['prix'] * $it['quantite'], 2) ?> TND</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="4" style="text-align:right;"><strong>Total</strong></td><td><strong><?= number_format($total, 2) ?> TND</strong></td></tr></tfoot>
    </table>
    </div>
</div>

<?php if ($ordonnance['statut'] === 'en_attente'): ?>
    <div class="actions">
        <a class="btn btn-success" href="index.php?c=ordonnance&a=valider&id=<?= $ordonnance['id'] ?>">Valider l'ordonnance</a>
        <a class="btn btn-danger" href="index.php?c=ordonnance&a=refuser&id=<?= $ordonnance['id'] ?>">Refuser l'ordonnance</a>
    </div>
<?php endif; ?>

<p style="margin-top:16px;"><a href="index.php?c=ordonnance&a=index">← Retour a la liste</a></p>
