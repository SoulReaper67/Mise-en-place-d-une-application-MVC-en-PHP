<?php use App\Http\Web; ?>
<h1 class="h2 mb-4">Utilisateurs</h1>
<table class="table table-striped bg-white">
    <thead class="table-dark"><tr><th scope="col">Nom</th><th scope="col">Prénom</th><th scope="col">Email</th><th scope="col">Téléphone</th><th scope="col">Rôle</th></tr></thead>
    <tbody>
    <?php foreach ($employees as $employee): ?>
        <tr><td><?= Web::e($employee['nom']) ?></td><td><?= Web::e($employee['prenom']) ?></td><td><?= Web::e($employee['email']) ?></td><td><?= Web::e($employee['telephone']) ?></td><td><?= Web::e($employee['role']) ?></td></tr>
    <?php endforeach ?>
    </tbody>
</table>
