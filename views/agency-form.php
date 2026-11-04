<?php use App\Http\Web; ?>
<h1 class="h2 mb-4"><?= $edit ? 'Modifier une agence' : 'Créer une agence' ?></h1>
<?php if (isset($error)): ?><div class="alert alert-danger" role="alert"><?= Web::e($error) ?></div><?php endif ?>
<form class="card p-4 shadow-sm" method="post" action="<?= $edit ? '/admin/agences/modifier?id=' . (int) $agency['id'] : '/admin/agences/nouvelle' ?>">
    <input type="hidden" name="csrf" value="<?= Web::e(Web::token()) ?>">
    <label for="nom" class="form-label">Nom de la ville</label>
    <input class="form-control mb-4" id="nom" name="nom" value="<?= Web::e($agency['nom'] ?? '') ?>" maxlength="100" required>
    <div><button class="btn btn-primary">Enregistrer</button> <a class="btn btn-outline-secondary" href="/admin/agences">Annuler</a></div>
</form>
