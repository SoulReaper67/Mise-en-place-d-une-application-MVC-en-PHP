<?php use App\Http\Web; ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Touche pas au klaxon</title>
    <link rel="stylesheet" href="/assets/site.css">
    <script src="/assets/bootstrap.bundle.min.js" defer></script>
</head>
<body>
<header>
    <nav class="container d-flex align-items-center justify-content-between gap-3 py-3" aria-label="Navigation principale">
        <a class="fw-bold fs-5 text-secondary text-decoration-none" href="<?= ($user['role'] ?? '') === 'admin' ? '/admin' : '/' ?>">Touche pas au klaxon</a>
        <div class="d-flex align-items-center gap-3">
            <?php if ($user === null): ?>
                <a class="btn btn-primary" href="/connexion">Connexion</a>
            <?php else: ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/admin/utilisateurs">Utilisateurs</a>
                    <a href="/admin/agences">Agences</a>
                    <a href="/admin/trajets">Trajets</a>
                <?php else: ?>
                    <a class="btn btn-primary" href="/trajets/nouveau">Créer un trajet</a>
                <?php endif ?>
                <span>Bonjour <?= Web::e($user['prenom'] . ' ' . $user['nom']) ?></span>
                <form method="post" action="/deconnexion">
                    <input type="hidden" name="csrf" value="<?= Web::e(Web::token()) ?>">
                    <button class="btn btn-outline-secondary">Déconnexion</button>
                </form>
            <?php endif ?>
        </div>
    </nav>
</header>
<main class="container py-5">
    <?php if (is_string($flash)): ?><div class="alert alert-info" role="status"><?= Web::e($flash) ?></div><?php endif ?>
    <?php require __DIR__ . '/' . $page . '.php'; ?>
</main>
<footer class="text-center py-4 text-secondary">© <?= date('Y') ?> Touche pas au klaxon</footer>
</body>
</html>
