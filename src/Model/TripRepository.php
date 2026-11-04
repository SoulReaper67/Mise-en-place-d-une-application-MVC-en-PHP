<?php
declare(strict_types=1);

namespace App\Model;

use PDO;

/** Requêtes de consultation et d’écriture des trajets. */
final class TripRepository
{
    private const SELECT = 'SELECT t.*, d.nom AS origine, a.nom AS destination,
        e.nom, e.prenom, e.email, e.telephone
        FROM trajets t JOIN agences d ON d.id = t.agence_depart_id
        JOIN agences a ON a.id = t.agence_arrivee_id
        JOIN employes e ON e.id = t.auteur_id';

    public function __construct(private PDO $pdo) {}

    /** @return list<array<string,mixed>> */
    public function all(bool $admin = false): array
    {
        $where = $admin ? '' : ' WHERE t.depart >= ? AND t.places_disponibles > 0';
        $query = $this->pdo->prepare(self::SELECT . $where . ' ORDER BY t.depart, t.id');
        $query->execute($admin ? [] : [date('Y-m-d H:i:s')]);
        return $query->fetchAll();
    }

    /** @return array<string,mixed>|false */
    public function find(int $id): array|false
    {
        $query = $this->pdo->prepare(self::SELECT . ' WHERE t.id = ?');
        $query->execute([$id]);
        return $query->fetch();
    }

    /** @param array{int,int,string,string,int,int} $values */
    public function save(?int $id, int $author, array $values): void
    {
        if ($id === null) {
            $this->pdo->prepare('INSERT INTO trajets
                (agence_depart_id, agence_arrivee_id, depart, arrivee, places_totales, places_disponibles, auteur_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([...$values, $author]);
            return;
        }
        $this->pdo->prepare('UPDATE trajets SET agence_depart_id = ?, agence_arrivee_id = ?,
            depart = ?, arrivee = ?, places_totales = ?, places_disponibles = ? WHERE id = ?')
            ->execute([...$values, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM trajets WHERE id = ?')->execute([$id]);
    }
}
