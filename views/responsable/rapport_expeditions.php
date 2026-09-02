<h1>Rapport - Expeditions / Ventes</h1>

<div class="filter-bar">
    <form method="GET" action="index.php">
        <input type="hidden" name="c" value="transaction">
        <input type="hidden" name="a" value="rapportExpeditions">
        <div class="form-group" style="max-width:220px;">
            <label for="jours">Periode (jours)</label>
            <select id="jours" name="jours" onchange="this.form.submit()">
                <option value="7" <?= $jours == 7 ? 'selected' : '' ?>>7 derniers jours</option>
                <option value="30" <?= $jours == 30 ? 'selected' : '' ?>>30 derniers jours</option>
                <option value="90" <?= $jours == 90 ? 'selected' : '' ?>>90 derniers jours</option>
                <option value="365" <?= $jours == 365 ? 'selected' : '' ?>>1 an</option>
            </select>
        </div>
    </form>
</div>

<div class="actions no-print" style="margin-bottom:14px;">
    <a class="btn btn-outline btn-sm" href="index.php?c=transaction&a=exportExpeditionsCsv&jours=<?= (int) $jours ?>">Exporter en CSV</a>
    <button type="button" class="btn btn-outline btn-sm" data-print>Imprimer / PDF</button>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Client</th><th>Pharmacien</th><th>Montant</th><th>Statut</th><th>Date</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($transactions)): ?>
            <tr><td colspan="7">Aucune expedition sur cette periode.</td></tr>
        <?php endif; ?>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td>#<?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['client_nom']) ?></td>
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
