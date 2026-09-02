<h1>Tableau de bord - Pharmacien</h1>

<div class="stats-grid">
    <div class="stat-card warning">
        <div class="label">Ordonnances en attente de validation</div>
        <div class="value"><?= count($ordonnancesAttente) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Mes transactions traitees</div>
        <div class="value"><?= count($mesTransactions) ?></div>
    </div>
</div>

<div class="card">
    <h2>Ordonnances a traiter</h2>
    <?php if (empty($ordonnancesAttente)): ?>
        <p>Aucune ordonnance en attente pour le moment.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Client</th><th>Medecin</th><th>Type</th><th>Date prescription</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($ordonnancesAttente, 0, 8) as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['client_nom']) ?></td>
                    <td><?= htmlspecialchars($o['medecin_nom']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($o['type'])) ?></td>
                    <td><?= htmlspecialchars($o['date_prescription']) ?></td>
                    <td><a class="btn btn-sm btn-primary" href="index.php?c=ordonnance&a=voir&id=<?= $o['id'] ?>">Consulter</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <p style="margin-top:10px;"><a href="index.php?c=ordonnance&a=index">Voir toutes les ordonnances →</a></p>
    <?php endif; ?>
</div>
