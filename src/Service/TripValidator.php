<?php
declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;
use InvalidArgumentException;

/** Vérifie les données métier d’un trajet indépendamment de la base. */
final class TripValidator
{
    /**
     * @param array<string,mixed> $input
     * @return array{int,int,string,string,int,int}
     */
    public static function validate(array $input): array
    {
        $origin = self::integer($input['agence_depart_id'] ?? null, 1, PHP_INT_MAX);
        $destination = self::integer($input['agence_arrivee_id'] ?? null, 1, PHP_INT_MAX);
        if ($origin === $destination) {
            throw new InvalidArgumentException('Choisissez deux agences différentes.');
        }
        $departure = self::date($input['depart'] ?? null);
        $arrival = self::date($input['arrivee'] ?? null);
        if ($departure <= new DateTimeImmutable() || $arrival <= $departure) {
            throw new InvalidArgumentException('Le départ doit être à venir et l’arrivée doit suivre le départ.');
        }
        $total = self::integer($input['places_totales'] ?? null, 1, 65535);
        $available = self::integer($input['places_disponibles'] ?? null, 0, $total);
        return [$origin, $destination, $departure->format('Y-m-d H:i:s'), $arrival->format('Y-m-d H:i:s'), $total, $available];
    }

    private static function integer(mixed $value, int $min, int $max): int
    {
        $result = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        if ($result === false) {
            throw new InvalidArgumentException('Vérifiez les agences et les nombres de places.');
        }
        return $result;
    }

    private static function date(mixed $value): DateTimeImmutable
    {
        $date = is_string($value) ? DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value) : false;
        if ($date === false || $date->format('Y-m-d\TH:i') !== $value) {
            throw new InvalidArgumentException('Renseignez des dates et heures valides.');
        }
        return $date;
    }
}
