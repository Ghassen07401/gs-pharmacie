<div class="topbar">
    <h1>Gestion des utilisateurs</h1>
    <a class="btn btn-primary" href="index.php?c=utilisateur&a=ajouter">+ Nouvel utilisateur</a>
</div>

<div class="filter-bar">
    <form method="GET" action="index.php">
        <input type="hidden" name="c" value="utilisateur">
        <input type="hidden" name="a" value="index">
        <div class="form-row">
            <div class="form-group">
                <label for="terme">Recherche (nom, prenom, email)</label>
                <input type="text" id="terme" name="terme" value="<?= htmlspecialchars($terme) ?>">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="">Tous</option>
                    <option value="responsable" <?= $role === 'responsable' ? 'selected' : '' ?>>Responsable</option>
                    <option value="pharmacien" <?= $role === 'pharmacien' ? 'selected' : '' ?>>Pharmacien</option>
                    <option value="client" <?= $role === 'client' ? 'selected' : '' ?>>Client</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-accent">Rechercher</button>
        <a class="btn btn-outline" href="index.php?c=utilisateur&a=index">Reinitialiser</a>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Nom complet</th><th>Email</th><th>Role</th><th>Telephone</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($utilisateurs)): ?>
            <tr><td colspan="6">Aucun utilisateur trouve.</td></tr>
        <?php endif; ?>
        <?php foreach ($utilisateurs as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                <td><?= htmlspecialchars($u['telephone'] ?? '-') ?></td>
                <td><?= $u['actif'] ? '<span class="badge badge-validee">Actif</span>' : '<span class="badge badge-refusee">Inactif</span>' ?></td>
                <td class="actions">
                    <a class="btn btn-sm btn-outline" href="index.php?c=utilisateur&a=modifier&id=<?= $u['id'] ?>">Modifier</a>
                    <?php if ((int) $u['id'] !== Auth::id()): ?>
                        <form method="POST" action="index.php?c=utilisateur&a=supprimer&id=<?= $u['id'] ?>" style="display:inline;"><?= Csrf::field() ?><button type="submit" class="btn btn-sm btn-danger" data-confirm="Supprimer cet utilisateur ?">Supprimer</button></form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
