<?php
declare(strict_types=1);

namespace App\Model;

use PDO;

/** Accès aux agences importées ou administrées. */
final class AgencyRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return list<array{id:int,nom:string}> */
    public function all(): array
    {
        return $this->pdo->query('SELECT id, nom FROM agences ORDER BY nom')->fetchAll();
    }

    /** @return array{id:int,nom:string}|false */
    public function find(int $id): array|false
    {
        $query = $this->pdo->prepare('SELECT id, nom FROM agences WHERE id = ?');
        $query->execute([$id]);
        return $query->fetch();
    }

    public function save(?int $id, string $name): void
    {
        if ($id === null) {
            $this->pdo->prepare('INSERT INTO agences (nom) VALUES (?)')->execute([$name]);
            return;
        }
        $this->pdo->prepare('UPDATE agences SET nom = ? WHERE id = ?')->execute([$name, $id]);
    }

    /** Les clés étrangères empêchent la suppression d’une agence utilisée. */
    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM agences WHERE id = ?')->execute([$id]);
    }
}
