<h1>Medicaments disponibles</h1>

<div class="filter-bar">
    <form method="GET" action="index.php">
        <input type="hidden" name="c" value="medicament">
        <input type="hidden" name="a" value="catalogue">
        <div class="form-group">
            <label for="nom">Rechercher un medicament</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($terme) ?>" placeholder="Nom du medicament...">
        </div>
        <button type="submit" class="btn btn-accent">Rechercher</button>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Nom</th><th>Categorie</th><th>Description</th><th>Prix</th><th>Disponibilite</th><th>Ordonnance requise</th></tr></thead>
        <tbody>
        <?php if (empty($medicaments)): ?>
            <tr><td colspan="6">Aucun medicament trouve.</td></tr>
        <?php endif; ?>
        <?php foreach ($medicaments as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['nom']) ?></td>
                <td><?= htmlspecialchars($m['categorie']) ?></td>
                <td><?= htmlspecialchars($m['description'] ?? '-') ?></td>
                <td><?= number_format((float) $m['prix'], 2) ?> TND</td>
                <td><?= $m['stock'] > 0 ? '<span class="badge badge-validee">Disponible</span>' : '<span class="badge badge-refusee">Rupture</span>' ?></td>
                <td><?= $m['necessite_ordonnance'] ? 'Oui' : 'Non' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
