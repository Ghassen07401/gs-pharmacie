<div class="topbar">
    <h1>Mon historique d'ordonnances</h1>
    <a class="btn btn-primary" href="index.php?c=ordonnance&a=soumettre">+ Nouvelle ordonnance</a>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Medecin</th><th>Date</th><th>Type</th><th>Statut</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($ordonnances)): ?>
            <tr><td colspan="6">Vous n'avez pas encore soumis d'ordonnance.</td></tr>
        <?php endif; ?>
        <?php foreach ($ordonnances as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['medecin_nom']) ?></td>
                <td><?= htmlspecialchars($o['date_prescription']) ?></td>
                <td><?= htmlspecialchars(ucfirst($o['type'])) ?></td>
                <td><span class="badge badge-<?= $o['statut'] === 'en_attente' ? 'attente' : $o['statut'] ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $o['statut']))) ?></span></td>
                <td><a href="index.php?c=ordonnance&a=voir&id=<?= $o['id'] ?>">Voir details</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
