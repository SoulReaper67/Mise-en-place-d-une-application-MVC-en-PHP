<?php use App\Http\Web; ?>
<div class="card shadow-sm mx-auto" style="max-width: 28rem">
    <div class="card-body p-4">
        <h1 class="h3 mb-4">Connexion</h1>
        <?php if (isset($error)): ?><div class="alert alert-danger" role="alert"><?= Web::e($error) ?></div><?php endif ?>
        <form method="post" action="/connexion">
            <input type="hidden" name="csrf" value="<?= Web::e(Web::token()) ?>">
            <label for="email" class="form-label">Adresse email</label>
            <input id="email" name="email" type="email" class="form-control mb-3" autocomplete="username" required>
            <label for="password" class="form-label">Mot de passe</label>
            <input id="password" name="password" type="password" class="form-control mb-4" autocomplete="current-password" required>
            <button class="btn btn-primary">Se connecter</button>
        </form>
    </div>
</div>
