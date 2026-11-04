<?php
declare(strict_types=1);

/** Crée les tables dans la base vide configurée par DB_DSN. */
$pdo = require dirname(__DIR__) . '/config/database.php';
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    throw new RuntimeException('Script SQL introuvable.');
}
foreach (explode(';', $schema) as $statement) {
    if (trim($statement) !== '') {
        $pdo->exec($statement);
    }
}
echo "Schéma installé.\n";
