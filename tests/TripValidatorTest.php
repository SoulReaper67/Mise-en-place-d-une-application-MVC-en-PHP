<?php
declare(strict_types=1);

use App\Service\TripValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** Contrôles métier avant toute écriture. */
final class TripValidatorTest extends TestCase
{
    public function testValidTrip(): void
    {
        self::assertSame([1, 2, '2099-02-01 08:00:00', '2099-02-01 10:00:00', 4, 2], TripValidator::validate(self::input()));
    }

    #[DataProvider('invalidInputs')]
    public function testRejectsInvalidTrip(string $field, mixed $value): void
    {
        $input = self::input();
        $input[$field] = $value;
        $this->expectException(InvalidArgumentException::class);
        TripValidator::validate($input);
    }

    /** @return iterable<string,array{string,mixed}> */
    public static function invalidInputs(): iterable
    {
        yield 'même agence' => ['agence_arrivee_id', 1];
        yield 'arrivée avant départ' => ['arrivee', '2099-02-01T07:00'];
        yield 'date impossible' => ['arrivee', '2099-02-30T10:00'];
        yield 'trajet passé' => ['depart', '2000-01-01T00:00'];
        yield 'places disponibles au-delà du total' => ['places_disponibles', 5];
        yield 'places négatives' => ['places_disponibles', -1];
        yield 'total nul' => ['places_totales', 0];
        yield 'identifiant mal formé' => ['agence_depart_id', ['1']];
    }

    /** @return array<string,int|string> */
    private static function input(): array
    {
        return ['agence_depart_id' => 1, 'agence_arrivee_id' => 2, 'depart' => '2099-02-01T08:00',
            'arrivee' => '2099-02-01T10:00', 'places_totales' => 4, 'places_disponibles' => 2];
    }
}
