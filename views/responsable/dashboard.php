<h1>Tableau de bord - Responsable Pharmacie</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="label">Medicaments references</div>
        <div class="value"><?= (int) $nbMedicaments ?></div>
    </div>
    <div class="stat-card danger">
        <div class="label">Medicaments en stock critique</div>
        <div class="value"><?= count($stockCritique) ?></div>
    </div>
    <div class="stat-card danger">
        <div class="label">Medicaments perimes</div>
        <div class="value"><?= (int) $nbPerimes ?></div>
    </div>
    <div class="stat-card warning">
        <div class="label">Ordonnances en attente</div>
        <div class="value"><?= (int) $ordonnancesAttente ?></div>
    </div>
    <div class="stat-card">
        <div class="label">Expeditions (7 derniers jours)</div>
        <div class="value"><?= count($expeditionsRecentes) ?></div>
    </div>
</div>

<?php if (!empty($bientotPerimes)): ?>
<div class="card">
    <h2>Peremptions a surveiller (90 jours)</h2>
    <ul>
        <?php foreach (array_slice($bientotPerimes, 0, 5) as $m): ?>
            <li>
                <strong><?= htmlspecialchars($m['nom']) ?></strong> —
                expire le <?= htmlspecialchars($m['date_expiration']) ?>
                (dans <?= (int) $m['jours_restants'] ?> jours, <?= (int) $m['stock'] ?> en stock)
            </li>
        <?php endforeach; ?>
    </ul>
    <a class="btn btn-sm btn-outline" href="index.php?c=medicament&a=peremptions">Voir le rapport complet</a>
</div>
<?php endif; ?>

<div class="card">
    <h2>Stocks critiques</h2>
    <?php if (empty($stockCritique)): ?>
        <p>Aucun medicament en stock critique actuellement.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead><tr><th>Medicament</th><th>Categorie</th><th>Stock</th><th>Seuil min.</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($stockCritique, 0, 5) as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['nom']) ?></td>
                    <td><?= htmlspecialchars($m['categorie']) ?></td>
                    <td><?= (int) $m['stock'] ?></td>
                    <td><?= (int) $m['stock_minimum'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <p style="margin-top:10px;"><a href="index.php?c=medicament&a=stockCritique">Voir le rapport complet →</a></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Actions rapides</h2>
    <div class="actions">
        <a class="btn btn-primary" href="index.php?c=medicament&a=ajouter">+ Ajouter un medicament</a>
        <a class="btn btn-accent" href="index.php?c=ordonnance&a=index">Traiter les ordonnances</a>
        <a class="btn btn-outline" href="index.php?c=utilisateur&a=ajouter">+ Creer un utilisateur</a>
    </div>
</div>
