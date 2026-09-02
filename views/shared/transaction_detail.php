<h1>Transaction #<?= $transaction['id'] ?></h1>

<div class="card">
    <p><strong>Client :</strong> <?= htmlspecialchars($transaction['client_nom']) ?></p>
    <p><strong>Pharmacien :</strong> <?= htmlspecialchars($transaction['pharmacien_nom']) ?></p>
    <p><strong>Date :</strong> <?= htmlspecialchars($transaction['date_transaction']) ?></p>
    <p><strong>Statut :</strong> <?= htmlspecialchars(ucfirst($transaction['statut'])) ?></p>
    <?php if (!empty($transaction['ordonnance_id'])): ?>
        <p><strong>Ordonnance liee :</strong> <a href="index.php?c=ordonnance&a=voir&id=<?= $transaction['ordonnance_id'] ?>">#<?= $transaction['ordonnance_id'] ?></a></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Detail des medicaments</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th>Medicament</th><th>Quantite</th><th>Prix unitaire</th><th>Sous-total</th></tr></thead>
        <tbody>
        <?php foreach ($details as $d): ?>
            <tr>
                <td><?= htmlspecialchars($d['medicament_nom']) ?></td>
                <td><?= (int) $d['quantite'] ?></td>
                <td><?= number_format((float) $d['prix_unitaire'], 2) ?> TND</td>
                <td><?= number_format($d['prix_unitaire'] * $d['quantite'], 2) ?> TND</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="3" style="text-align:right;"><strong>Total</strong></td><td><strong><?= number_format((float) $transaction['montant_total'], 2) ?> TND</strong></td></tr>
        </tfoot>
    </table>
    </div>
</div>

<a class="btn btn-outline" href="index.php?c=dashboard&a=index">← Retour au tableau de bord</a>
