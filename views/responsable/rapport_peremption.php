<h1>Rapport - Peremptions</h1>
<p>
    Suivi des dates de peremption du stock. Un medicament perime reste physiquement
    en rayon mais ne peut plus etre delivre : il est automatiquement retire du catalogue
    client et sa validation est bloquee cote pharmacien.
</p>

<div class="filter-bar no-print">
    <form method="GET" action="index.php">
        <input type="hidden" name="c" value="medicament">
        <input type="hidden" name="a" value="peremptions">
        <div class="form-group" style="max-width:220px;">
            <label for="jours">Fenetre de surveillance</label>
            <select id="jours" name="jours" onchange="this.form.submit()">
                <option value="30"  <?= $jours == 30  ? 'selected' : '' ?>>30 prochains jours</option>
                <option value="90"  <?= $jours == 90  ? 'selected' : '' ?>>90 prochains jours</option>
                <option value="180" <?= $jours == 180 ? 'selected' : '' ?>>6 prochains mois</option>
                <option value="365" <?= $jours == 365 ? 'selected' : '' ?>>12 prochains mois</option>
            </select>
        </div>
    </form>
</div>

<div class="actions no-print" style="margin-bottom:14px;">
    <a class="btn btn-outline btn-sm" href="index.php?c=medicament&a=exportPeremptionsCsv&jours=<?= (int) $jours ?>">Exporter en CSV</a>
    <button type="button" class="btn btn-outline btn-sm" data-print>Imprimer / PDF</button>
</div>

<div class="card">
    <h2>Medicaments perimes - retrait immediat</h2>
    <?php if (empty($perimes)): ?>
        <p>Aucun medicament perime en stock. 🎉</p>
    <?php else: ?>
        <div class="interaction-alert">
            <h3>⚠ <?= count($perimes) ?> medicament(s) perime(s) encore en stock</h3>
            <p style="margin:4px 0 0;font-size:0.9rem;">
                Ces produits doivent etre retires du rayon et detruits selon la procedure
                en vigueur. Ils sont deja bloques a la delivrance par l'application.
            </p>
        </div>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom</th><th>Categorie</th><th>Fabricant</th>
                    <th>Stock concerne</th><th>Date d'expiration</th><th>Perime depuis</th><th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($perimes as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['nom']) ?></td>
                    <td><?= htmlspecialchars($m['categorie']) ?></td>
                    <td><?= htmlspecialchars($m['fabricant'] ?? '-') ?></td>
                    <td><?= (int) $m['stock'] ?></td>
                    <td><?= htmlspecialchars($m['date_expiration']) ?></td>
                    <td><span class="badge badge-refusee"><?= (int) $m['jours_depuis_expiration'] ?> jours</span></td>
                    <td class="no-print">
                        <a class="btn btn-sm btn-outline" href="index.php?c=medicament&a=modifier&id=<?= $m['id'] ?>">Traiter</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-top:20px;">
    <h2>Peremptions a venir - <?= (int) $jours ?> prochains jours</h2>
    <?php if (empty($bientot)): ?>
        <p>Aucune peremption prevue sur cette periode.</p>
    <?php else: ?>
        <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom</th><th>Categorie</th><th>Fabricant</th>
                    <th>Stock</th><th>Date d'expiration</th><th>Echeance</th><th class="no-print">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bientot as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['nom']) ?></td>
                    <td><?= htmlspecialchars($m['categorie']) ?></td>
                    <td><?= htmlspecialchars($m['fabricant'] ?? '-') ?></td>
                    <td><?= (int) $m['stock'] ?></td>
                    <td><?= htmlspecialchars($m['date_expiration']) ?></td>
                    <td>
                        <span class="badge <?= (int) $m['jours_restants'] <= 30 ? 'badge-refusee' : 'badge-attente' ?>">
                            dans <?= (int) $m['jours_restants'] ?> jours
                        </span>
                    </td>
                    <td class="no-print">
                        <a class="btn btn-sm btn-outline" href="index.php?c=medicament&a=modifier&id=<?= $m['id'] ?>">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>
</div>
