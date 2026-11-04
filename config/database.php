<?php
declare(strict_types=1);

return new PDO(
    getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=klaxon;charset=utf8mb4',
    getenv('DB_USER') ?: 'klaxon',
    getenv('DB_PASSWORD') ?: '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
