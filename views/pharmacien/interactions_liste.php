<div class="topbar">
    <h1>Interactions medicamenteuses</h1>
    <a class="btn btn-primary" href="index.php?c=interaction&a=ajouter">+ Enregistrer une interaction</a>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Medicament 1</th><th>Medicament 2</th><th>Gravite</th><th>Description</th><th>Enregistre par</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($interactions)): ?>
            <tr><td colspan="6">Aucune interaction enregistree.</td></tr>
        <?php endif; ?>
        <?php foreach ($interactions as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['med1_nom']) ?></td>
                <td><?= htmlspecialchars($i['med2_nom']) ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($i['niveau_gravite']) ?>"><?= htmlspecialchars(ucfirst($i['niveau_gravite'])) ?></span></td>
                <td><?= htmlspecialchars($i['description']) ?></td>
                <td><?= htmlspecialchars($i['enregistre_par_nom'] ?? '-') ?></td>
                <td>
                    <form method="POST" action="index.php?c=interaction&a=supprimer&id=<?= $i['id'] ?>" style="display:inline;"><?= Csrf::field() ?><button type="submit" class="btn btn-sm btn-danger" data-confirm="Supprimer cette interaction ?">Supprimer</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
