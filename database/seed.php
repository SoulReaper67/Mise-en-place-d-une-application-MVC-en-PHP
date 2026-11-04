<?php
declare(strict_types=1);

/**
 * Charge le jeu de démonstration. Les exports RH d’origine restent locaux.
 * Option --annexes : importer aussi les deux fichiers dans database/import/.
 */
date_default_timezone_set('Europe/Paris');
$pdo = require dirname(__DIR__) . '/config/database.php';
$import = in_array('--annexes', $argv, true);
$cities = require __DIR__ . '/demo-agencies.php';
$employees = [
    ['Démo', 'Camille', '0100000001', 'employe@example.test', 'employe', 'Klaxon-Employe-2026!'],
    ['Démo', 'Alex', '0100000002', 'admin@example.test', 'admin', 'Klaxon-Admin-2026!'],
];
if ($import) {
    $cityLines = file(__DIR__ . '/import/agences.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $employeeLines = file(__DIR__ . '/import/users.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($cityLines === false || $employeeLines === false) {
        throw new RuntimeException('Copiez les deux fichiers d’annexe dans database/import/.');
    }
    $cities = array_map('trim', $cityLines);
    foreach ($employeeLines as $line) {
        $fields = array_map('trim', str_getcsv($line, ',', '"', ''));
        if (count($fields) !== 4 || !filter_var($fields[3], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Une ligne du fichier RH est invalide.');
        }
        $employees[] = [...$fields, 'employe', bin2hex(random_bytes(24))];
    }
}

$pdo->beginTransaction();
try {
    $cityInsert = $pdo->prepare('INSERT INTO agences (nom) VALUES (?) ON DUPLICATE KEY UPDATE id = id');
    foreach ($cities as $city) {
        $cityInsert->execute([$city]);
    }
    $employeeInsert = $pdo->prepare('INSERT INTO employes (nom, prenom, telephone, email, role, mot_de_passe)
        VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = id');
    foreach ($employees as [$name, $firstName, $phone, $email, $role, $password]) {
        $employeeInsert->execute([$name, $firstName, $phone, $email, $role, password_hash($password, PASSWORD_DEFAULT)]);
    }

    $cityIds = $pdo->query('SELECT nom, id FROM agences')->fetchAll(PDO::FETCH_KEY_PAIR);
    $author = $pdo->query("SELECT id FROM employes WHERE email = 'employe@example.test'")->fetchColumn();
    $insert = $pdo->prepare('INSERT INTO trajets
        (agence_depart_id, agence_arrivee_id, depart, arrivee, places_totales, places_disponibles, auteur_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)');
    if (!$pdo->query('SELECT 1 FROM trajets LIMIT 1')->fetchColumn()) {
        foreach ([
            ['Paris', 'Lyon', '+1 day', '08:30', '13:00', 4, 2],
            ['Lyon', 'Marseille', '+2 days', '10:00', '13:30', 3, 1],
            ['Marseille', 'Paris', '+3 days', '09:00', '17:00', 4, 0],
            ['Lyon', 'Paris', '-1 day', '08:00', '12:30', 4, 3],
        ] as [$origin, $destination, $offset, $departureTime, $arrivalTime, $total, $available]) {
            $day = (new DateTimeImmutable($offset))->format('Y-m-d');
            $insert->execute([$cityIds[$origin], $cityIds[$destination], "$day $departureTime:00", "$day $arrivalTime:00", $total, $available, $author]);
        }
    }
    $pdo->commit();
    echo "Données initialisées.\n";
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
