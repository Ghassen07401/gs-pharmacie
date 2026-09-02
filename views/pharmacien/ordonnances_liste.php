<h1>Ordonnances</h1>

<div class="filter-bar">
    <form method="GET" action="index.php">
        <input type="hidden" name="c" value="ordonnance">
        <input type="hidden" name="a" value="index">
        <div class="form-group" style="max-width:260px;">
            <label for="statut">Filtrer par statut</label>
            <select id="statut" name="statut" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="en_attente" <?= $statut === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="validee" <?= $statut === 'validee' ? 'selected' : '' ?>>Validees</option>
                <option value="refusee" <?= $statut === 'refusee' ? 'selected' : '' ?>>Refusees</option>
            </select>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>#</th><th>Client</th><th>Medecin</th><th>Type</th><th>Date prescription</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($ordonnances)): ?>
            <tr><td colspan="7">Aucune ordonnance trouvee.</td></tr>
        <?php endif; ?>
        <?php foreach ($ordonnances as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['client_nom']) ?></td>
                <td><?= htmlspecialchars($o['medecin_nom']) ?></td>
                <td><?= htmlspecialchars(ucfirst($o['type'])) ?></td>
                <td><?= htmlspecialchars($o['date_prescription']) ?></td>
                <td><span class="badge badge-<?= $o['statut'] === 'en_attente' ? 'attente' : $o['statut'] ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $o['statut']))) ?></span></td>
                <td class="actions">
                    <a class="btn btn-sm btn-outline" href="index.php?c=ordonnance&a=voir&id=<?= $o['id'] ?>">Consulter</a>
                    <?php if ($o['statut'] === 'en_attente'): ?>
                        <a class="btn btn-sm btn-success" href="index.php?c=ordonnance&a=valider&id=<?= $o['id'] ?>">Valider</a>
                        <a class="btn btn-sm btn-danger" href="index.php?c=ordonnance&a=refuser&id=<?= $o['id'] ?>">Refuser</a>
                    <?php endif; ?>
                    <?php if (Auth::role() === 'responsable'): ?>
                        <form method="POST" action="index.php?c=ordonnance&a=supprimer&id=<?= $o['id'] ?>" style="display:inline;"><?= Csrf::field() ?><button type="submit" class="btn btn-sm btn-danger" data-confirm="Supprimer definitivement cette ordonnance ?">Supprimer</button></form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
