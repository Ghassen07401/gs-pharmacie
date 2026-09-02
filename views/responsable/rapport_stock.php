<h1>Rapport - Stocks critiques</h1>
<p>Medicaments dont le stock actuel est inferieur ou egal au seuil minimum defini.</p>

<div class="actions no-print" style="margin-bottom:14px;">
    <a class="btn btn-outline btn-sm" href="index.php?c=medicament&a=exportStockCsv">Exporter en CSV</a>
    <button type="button" class="btn btn-outline btn-sm" data-print>Imprimer / PDF</button>
</div>

<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Nom</th><th>Categorie</th><th>Stock actuel</th><th>Seuil minimum</th><th>Ecart</th><th>Action</th></tr></thead>
        <tbody>
        <?php if (empty($medicaments)): ?>
            <tr><td colspan="6">Aucun medicament en stock critique. 🎉</td></tr>
        <?php endif; ?>
        <?php foreach ($medicaments as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['nom']) ?></td>
                <td><?= htmlspecialchars($m['categorie']) ?></td>
                <td><span class="badge badge-refusee"><?= (int) $m['stock'] ?></span></td>
                <td><?= (int) $m['stock_minimum'] ?></td>
                <td><?= (int) $m['stock'] - (int) $m['stock_minimum'] ?></td>
                <td><a class="btn btn-sm btn-primary" href="index.php?c=medicament&a=modifier&id=<?= $m['id'] ?>">Reapprovisionner</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
