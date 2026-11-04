<?php
declare(strict_types=1);

use App\Model\AgencyRepository;
use App\Model\TripRepository;
use PHPUnit\Framework\TestCase;

/** Exécute les vraies requêtes sur une base dédiée, dans une transaction annulée. */
final class RepositoryTest extends TestCase
{
    private ?PDO $pdo = null;

    protected function setUp(): void
    {
        $dsn = getenv('TEST_DB_DSN');
        if (!$dsn) {
            self::markTestSkipped('Définissez TEST_DB_DSN pour les tests MariaDB/MySQL.');
        }
        $this->pdo = new PDO($dsn, getenv('TEST_DB_USER') ?: 'klaxon', getenv('TEST_DB_PASSWORD') ?: '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->pdo?->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    public function testAgencyCreateUpdateDelete(): void
    {
        $agencies = new AgencyRepository($this->pdo);
        $agencies->save(null, 'Agence test');
        $id = (int) $this->pdo->lastInsertId();
        self::assertSame('Agence test', $agencies->find($id)['nom']);
        $agencies->save($id, 'Agence renommée');
        self::assertSame('Agence renommée', $agencies->find($id)['nom']);
        $agencies->delete($id);
        self::assertFalse($agencies->find($id));
    }

    public function testTripCreateUpdateDeleteAndPublicFilter(): void
    {
        $agencies = new AgencyRepository($this->pdo);
        $agencies->save(null, 'Départ test');
        $origin = (int) $this->pdo->lastInsertId();
        $agencies->save(null, 'Arrivée test');
        $destination = (int) $this->pdo->lastInsertId();
        $this->pdo->exec("INSERT INTO employes (nom, prenom, telephone, email, mot_de_passe)
            VALUES ('Test','Alice','0100000000','alice@example.test','inutilise')");
        $author = (int) $this->pdo->lastInsertId();

        $trips = new TripRepository($this->pdo);
        $trips->save(null, $author, [$origin, $destination, '2099-01-01 08:00:00', '2099-01-01 10:00:00', 4, 2]);
        $id = (int) $this->pdo->lastInsertId();
        self::assertSame($author, (int) $trips->find($id)['auteur_id']);
        self::assertContains($id, array_column($trips->all(), 'id'));
        $trips->save($id, $author, [$origin, $destination, '2099-01-01 08:00:00', '2099-01-01 10:00:00', 4, 0]);
        self::assertNotContains($id, array_column($trips->all(), 'id'));
        self::assertContains($id, array_column($trips->all(true), 'id'));
        try {
            $agencies->delete($origin);
            self::fail('Une agence liée doit être protégée.');
        } catch (PDOException $exception) {
            self::assertSame('23000', (string) $exception->getCode());
        }
        $trips->delete($id);
        self::assertFalse($trips->find($id));
    }
}
