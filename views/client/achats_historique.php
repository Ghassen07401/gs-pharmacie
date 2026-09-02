<h1>Mon historique d'achats</h1>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Pharmacien</th><th>Montant</th><th>Statut</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($transactions)): ?>
            <tr><td colspan="6">Vous n'avez pas encore effectue d'achat.</td></tr>
        <?php endif; ?>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td>#<?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['pharmacien_nom']) ?></td>
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
