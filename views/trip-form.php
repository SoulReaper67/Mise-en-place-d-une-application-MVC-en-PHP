<?php
use App\Http\Web;
$contact = $edit ? $trip : $user;
$back = $user['role'] === 'admin' ? '/admin/trajets' : '/';
$action = $edit ? '/trajets/modifier?id=' . (int) $trip['id'] : '/trajets/nouveau';
?>
<h1 class="h2 mb-4"><?= $edit ? 'Modifier le trajet' : 'Créer un trajet' ?></h1>
<?php if (isset($error)): ?><div class="alert alert-danger" role="alert"><?= Web::e($error) ?></div><?php endif ?>
<form method="post" action="<?= Web::e($action) ?>" class="card p-4 shadow-sm">
    <input type="hidden" name="csrf" value="<?= Web::e(Web::token()) ?>">
    <fieldset class="mb-4">
        <legend class="h5">Personne à contacter</legend>
        <div class="row g-3">
            <?php foreach (['prenom' => 'Prénom', 'nom' => 'Nom', 'email' => 'Email', 'telephone' => 'Téléphone'] as $key => $label): ?>
                <div class="col-3"><label for="contact-<?= $key ?>" class="form-label"><?= $label ?></label>
                <input class="form-control" id="contact-<?= $key ?>" value="<?= Web::e($contact[$key] ?? '') ?>" readonly></div>
            <?php endforeach ?>
        </div>
    </fieldset>
    <fieldset class="mb-4">
        <legend class="h5">Itinéraire et horaires</legend>
        <div class="row g-3">
            <?php foreach (['depart' => 'Départ', 'arrivee' => 'Arrivée'] as $key => $label): ?>
                <?php $field = 'agence_' . $key . '_id'; ?>
                <div class="col-6">
                    <label for="<?= $field ?>" class="form-label">Agence de <?= $key === 'depart' ? 'départ' : 'destination' ?></label>
                    <select class="form-select" id="<?= $field ?>" name="<?= $field ?>" required>
                        <option value="">Sélectionner une agence</option>
                        <?php foreach ($agencies as $agency): ?>
                            <option value="<?= $agency['id'] ?>" <?= (string) ($trip[$field] ?? '') === (string) $agency['id'] ? 'selected' : '' ?>><?= Web::e($agency['nom']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
                <div class="col-6"><label for="<?= $key ?>" class="form-label"><?= $label ?> (date et heure)</label>
                    <input class="form-control" type="datetime-local" id="<?= $key ?>" name="<?= $key ?>" value="<?= Web::e(substr(str_replace(' ', 'T', is_string($trip[$key] ?? null) ? $trip[$key] : ''), 0, 16)) ?>" required></div>
            <?php endforeach ?>
        </div>
    </fieldset>
    <fieldset class="mb-4">
        <legend class="h5">Places</legend>
        <div class="row g-3">
            <?php foreach (['places_totales' => 'Places totales', 'places_disponibles' => 'Places disponibles'] as $key => $label): ?>
                <div class="col-6"><label for="<?= $key ?>" class="form-label"><?= $label ?></label>
                    <input class="form-control" type="number" id="<?= $key ?>" name="<?= $key ?>" min="<?= $key === 'places_totales' ? 1 : 0 ?>" max="65535" value="<?= Web::e($trip[$key] ?? '') ?>" required></div>
            <?php endforeach ?>
        </div>
    </fieldset>
    <div><button class="btn btn-primary">Enregistrer</button> <a class="btn btn-outline-secondary" href="<?= $back ?>">Annuler</a></div>
</form>
