<?php use App\Http\Web; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h2 mb-0"><?= $adminList ? 'Tous les trajets' : 'Trajets proposés' ?></h1>
    <?php if ($adminList): ?><a class="btn btn-primary" href="/trajets/nouveau">Créer un trajet</a><?php endif ?>
</div>
<?php if ($user === null): ?>
    <p class="text-secondary">Connectez-vous pour consulter les coordonnées de l’auteur d’un trajet.</p>
<?php endif ?>
<?php if (!$trips): ?>
    <p class="alert alert-info">Aucun trajet à afficher pour le moment.</p>
<?php else: ?>
<table class="table table-striped table-hover bg-white shadow-sm">
    <thead class="table-dark">
    <tr><th scope="col">Départ</th><th scope="col">Date</th><th scope="col">Heure</th><th scope="col">Destination</th><th scope="col">Date</th><th scope="col">Heure</th><th scope="col">Places libres</th><?php if ($user !== null): ?><th scope="col">Actions</th><?php endif ?></tr>
    </thead>
    <tbody>
    <?php foreach ($trips as $trip): ?>
        <?php $id = (int) $trip['id']; $canEdit = $user !== null && ($user['role'] === 'admin' || $user['id'] === (int) $trip['auteur_id']); ?>
        <tr>
            <td><?= Web::e($trip['origine']) ?></td>
            <td><?= date('d/m/Y', strtotime($trip['depart'])) ?></td>
            <td><?= date('H:i', strtotime($trip['depart'])) ?></td>
            <td><?= Web::e($trip['destination']) ?></td>
            <td><?= date('d/m/Y', strtotime($trip['arrivee'])) ?></td>
            <td><?= date('H:i', strtotime($trip['arrivee'])) ?></td>
            <td><?= (int) $trip['places_disponibles'] ?></td>
            <?php if ($user !== null): ?>
                <td class="text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#trajet-<?= $id ?>">Détails</button>
                    <?php if ($canEdit): ?>
                        <a class="btn btn-sm btn-outline-primary" href="/trajets/modifier?id=<?= $id ?>">Modifier</a>
                        <form class="d-inline" method="post" action="/trajets/supprimer?id=<?= $id ?>" onsubmit="return confirm('Supprimer ce trajet ?')">
                            <input type="hidden" name="csrf" value="<?= Web::e(Web::token()) ?>">
                            <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                        </form>
                    <?php endif ?>
                </td>
            <?php endif ?>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>
<?php endif ?>
<?php if ($user !== null): ?>
    <?php foreach ($trips as $trip): $id = (int) $trip['id']; ?>
    <div class="modal fade" id="trajet-<?= $id ?>" tabindex="-1" aria-labelledby="titre-<?= $id ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title h5" id="titre-<?= $id ?>"><?= Web::e($trip['origine']) ?> → <?= Web::e($trip['destination']) ?></h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button></div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <dt class="col-5">Auteur</dt><dd class="col-7"><?= Web::e($trip['prenom'] . ' ' . $trip['nom']) ?></dd>
                    <dt class="col-5">Téléphone</dt><dd class="col-7"><?= Web::e($trip['telephone']) ?></dd>
                    <dt class="col-5">Email</dt><dd class="col-7 text-break"><?= Web::e($trip['email']) ?></dd>
                    <dt class="col-5">Places totales</dt><dd class="col-7"><?= (int) $trip['places_totales'] ?></dd>
                </dl>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button></div>
        </div></div>
    </div>
    <?php endforeach ?>
<?php endif ?>
