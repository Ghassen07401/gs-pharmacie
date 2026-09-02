<h1>Refuser l'ordonnance #<?= $ordonnance['id'] ?></h1>

<div class="card">
    <p><strong>Client :</strong> <?= htmlspecialchars($ordonnance['client_nom'] ?? '') ?></p>
    <p><strong>Medecin :</strong> <?= htmlspecialchars($ordonnance['medecin_nom']) ?></p>

    <form method="POST" action="index.php?c=ordonnance&a=refuser&id=<?= $ordonnance['id'] ?>" data-validate novalidate>
        <?= Csrf::field() ?>
        <div class="form-group">
            <label for="commentaire">Motif du refus *</label>
            <textarea id="commentaire" name="commentaire" rows="3" required placeholder="Expliquez au client pourquoi l'ordonnance est refusee..."></textarea>
        </div>
        <div class="actions">
            <button type="submit" class="btn btn-danger">Confirmer le refus</button>
            <a class="btn btn-outline" href="index.php?c=ordonnance&a=voir&id=<?= $ordonnance['id'] ?>">Annuler</a>
        </div>
    </form>
</div>
