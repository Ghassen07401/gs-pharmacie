<h1>Mes transactions</h1>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Client</th><th>Montant</th><th>Statut</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($transactions)): ?>
            <tr><td colspan="6">Vous n'avez pas encore traite de transaction.</td></tr>
        <?php endif; ?>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td>#<?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['client_nom']) ?></td>
                <td><?= number_format((float) $t['montant_total'], 2) ?> TND</td>
                <td><?= htmlspecialchars(ucfirst($t['statut'])) ?></td>
                <td><?= htmlspecialchars($t['date_transaction']) ?></td>
                <td><a href="index.php?c=transaction&a=voir&id=<?= $t['id'] ?>">Details</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
