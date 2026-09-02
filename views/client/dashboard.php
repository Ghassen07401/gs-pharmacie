<h1>Bienvenue, <?= htmlspecialchars(Auth::fullName()) ?></h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Mes ordonnances</div>
        <div class="value"><?= count($mesOrdonnances) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Mes achats</div>
        <div class="value"><?= count($mesAchats) ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Medicaments disponibles</div>
        <div class="value"><?= count($medicaments) ?></div>
    </div>
</div>

<div class="card">
    <h2>Actions rapides</h2>
    <div class="actions">
        <a class="btn btn-primary" href="index.php?c=ordonnance&a=soumettre">+ Soumettre une ordonnance</a>
        <a class="btn btn-accent" href="index.php?c=medicament&a=catalogue">Voir le catalogue</a>
        <a class="btn btn-outline" href="index.php?c=transaction&a=historiqueAchats">Mon historique d'achats</a>
    </div>
</div>

<div class="card">
    <h2>Mes dernieres ordonnances</h2>
    <?php if (empty($mesOrdonnances)): ?>
        <p>Vous n'avez pas encore soumis d'ordonnance.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Medecin</th><th>Date</th><th>Type</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach (array_slice($mesOrdonnances, 0, 5) as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['medecin_nom']) ?></td>
                    <td><?= htmlspecialchars($o['date_prescription']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($o['type'])) ?></td>
                    <td><span class="badge badge-<?= htmlspecialchars(str_replace('_', '', $o['statut'] === 'en_attente' ? 'attente' : $o['statut'])) ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $o['statut']))) ?></span></td>
                    <td><a href="index.php?c=ordonnance&a=voir&id=<?= $o['id'] ?>">Voir</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
