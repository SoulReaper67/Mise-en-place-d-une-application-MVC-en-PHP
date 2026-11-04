<?php
declare(strict_types=1);

namespace App\Model;

use PDO;

/** Lecture seule des comptes issus du système RH. */
final class EmployeeRepository
{
    public function __construct(private PDO $pdo) {}

    /** @return list<array<string,mixed>> */
    public function all(): array
    {
        return $this->pdo->query('SELECT nom, prenom, email, telephone, role FROM employes ORDER BY nom, prenom')->fetchAll();
    }

    /** @return array<string,mixed>|false */
    public function findByEmail(string $email): array|false
    {
        $query = $this->pdo->prepare('SELECT * FROM employes WHERE email = ?');
        $query->execute([$email]);
        return $query->fetch();
    }
}
