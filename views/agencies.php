<?php use App\Http\Web; ?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h2 mb-0">Agences</h1><a class="btn btn-primary" href="/admin/agences/nouvelle">Créer une agence</a>
</div>
<table class="table table-striped bg-white">
    <thead class="table-dark"><tr><th scope="col">Ville</th><th scope="col">Actions</th></tr></thead>
    <tbody>
    <?php foreach ($agencies as $agency): ?>
        <tr><td><?= Web::e($agency['nom']) ?></td><td>
            <a class="btn btn-sm btn-outline-primary" href="/admin/agences/modifier?id=<?= $agency['id'] ?>">Modifier</a>
            <form class="d-inline" method="post" action="/admin/agences/supprimer?id=<?= $agency['id'] ?>" onsubmit="return confirm('Supprimer cette agence ?')">
                <input type="hidden" name="csrf" value="<?= Web::e(Web::token()) ?>">
                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
            </form>
        </td></tr>
    <?php endforeach ?>
    </tbody>
</table>
