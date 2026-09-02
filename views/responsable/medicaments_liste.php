<div class="topbar">
    <h1>Gestion des medicaments</h1>
    <a class="btn btn-primary" href="index.php?c=medicament&a=ajouter">+ Ajouter un medicament</a>
</div>

<div class="filter-bar">
    <form method="GET" action="index.php" data-validate novalidate>
        <input type="hidden" name="c" value="medicament">
        <input type="hidden" name="a" value="index">
        <div class="form-row">
            <div class="form-group">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($criteres['nom']) ?>" placeholder="Rechercher par nom...">
            </div>
            <div class="form-group">
                <label for="categorie">Categorie</label>
                <select id="categorie" name="categorie">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>" <?= $criteres['categorie'] === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="fabricant">Fabricant</label>
                <input type="text" id="fabricant" name="fabricant" value="<?= htmlspecialchars($criteres['fabricant']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="prix_min">Prix min (TND)</label>
                <input type="number" step="0.01" id="prix_min" name="prix_min" value="<?= htmlspecialchars($criteres['prix_min']) ?>" data-positive-number>
            </div>
            <div class="form-group">
                <label for="prix_max">Prix max (TND)</label>
                <input type="number" step="0.01" id="prix_max" name="prix_max" value="<?= htmlspecialchars($criteres['prix_max']) ?>" data-positive-number>
            </div>
            <div class="form-group">
                <label for="necessite_ordonnance">Necessite ordonnance</label>
                <select id="necessite_ordonnance" name="necessite_ordonnance">
                    <option value="">Indifferent</option>
                    <option value="1" <?= $criteres['necessite_ordonnance'] === '1' ? 'selected' : '' ?>>Oui</option>
                    <option value="0" <?= $criteres['necessite_ordonnance'] === '0' ? 'selected' : '' ?>>Non</option>
                </select>
            </div>
            <div class="form-group">
                <label for="stock_critique">Stock critique uniquement</label>
                <select id="stock_critique" name="stock_critique">
                    <option value="">Non</option>
                    <option value="1" <?= $criteres['stock_critique'] === '1' ? 'selected' : '' ?>>Oui</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-accent">Rechercher</button>
        <a class="btn btn-outline" href="index.php?c=medicament&a=index">Reinitialiser</a>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Nom</th><th>Categorie</th><th>Fabricant</th><th>Prix</th><th>Stock</th><th>Expiration</th><th>Ordonnance</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($medicaments)): ?>
            <tr><td colspan="8">Aucun medicament trouve.</td></tr>
        <?php endif; ?>
        <?php foreach ($medicaments as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['nom']) ?></td>
                <td><?= htmlspecialchars($m['categorie']) ?></td>
                <td><?= htmlspecialchars($m['fabricant'] ?? '-') ?></td>
                <td><?= number_format((float) $m['prix'], 2) ?> TND</td>
                <td>
                    <?= (int) $m['stock'] ?>
                    <?php if ((int) $m['stock'] <= (int) $m['stock_minimum']): ?>
                        <span class="badge badge-refusee">Critique</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= htmlspecialchars($m['date_expiration'] ?? '-') ?>
                    <?php $etat = Medicament::etatPeremption($m['date_expiration'] ?? null); ?>
                    <?php if ($etat === 'perime'): ?>
                        <span class="badge badge-refusee">Perime</span>
                    <?php elseif ($etat === 'bientot'): ?>
                        <span class="badge badge-attente">Expire dans <?= Medicament::joursAvantPeremption($m['date_expiration']) ?> j</span>
                    <?php endif; ?>
                </td>
                <td><?= $m['necessite_ordonnance'] ? 'Oui' : 'Non' ?></td>
                <td class="actions">
                    <a class="btn btn-sm btn-outline" href="index.php?c=medicament&a=modifier&id=<?= $m['id'] ?>">Modifier</a>
                    <form method="POST" action="index.php?c=medicament&a=supprimer&id=<?= $m['id'] ?>" style="display:inline;"><?= Csrf::field() ?><button type="submit" class="btn btn-sm btn-danger" data-confirm="Supprimer ce medicament ?">Supprimer</button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
