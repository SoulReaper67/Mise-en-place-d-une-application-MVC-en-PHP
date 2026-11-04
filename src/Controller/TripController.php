<?php
declare(strict_types=1);

namespace App\Controller;

use App\Http\Web;
use App\Model\AgencyRepository;
use App\Model\TripRepository;
use App\Service\TripValidator;
use InvalidArgumentException;
use PDOException;

/** Gère le catalogue des trajets et les modifications autorisées. */
final class TripController
{
    public function __construct(private TripRepository $trips, private AgencyRepository $agencies) {}

    public function index(): void
    {
        Web::view('trips', ['trips' => $this->trips->all(), 'adminList' => false]);
    }

    public function adminIndex(): void
    {
        Web::requireUser(true);
        Web::view('trips', ['trips' => $this->trips->all(true), 'adminList' => true]);
    }

    public function form(bool $edit = false): void
    {
        $user = Web::requireUser();
        $trip = $edit ? $this->editable(Web::id(), $user['id'], $user['role']) : [];
        $this->showForm($trip, $edit);
    }

    public function save(bool $edit = false): void
    {
        $user = Web::requireUser();
        Web::checkToken();
        $id = $edit ? Web::id() : null;
        $trip = $id === null ? [] : $this->editable($id, $user['id'], $user['role']);
        try {
            $values = TripValidator::validate($_POST);
            if (!$this->agencies->find($values[0]) || !$this->agencies->find($values[1])) {
                throw new InvalidArgumentException('Sélectionnez deux agences existantes.');
            }
            $this->trips->save($id, $user['id'], $values);
        } catch (InvalidArgumentException $exception) {
            http_response_code(422);
            $this->showForm($_POST + $trip, $edit, $exception->getMessage());
            return;
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }
            http_response_code(422);
            $this->showForm($_POST + $trip, $edit, 'Les agences ont changé. Vérifiez votre sélection.');
            return;
        }
        Web::redirect($user['role'] === 'admin' ? '/admin/trajets' : '/', $edit ? 'Le trajet a été modifié.' : 'Le trajet a été créé.');
    }

    public function delete(): void
    {
        $user = Web::requireUser();
        Web::checkToken();
        $id = Web::id();
        $this->editable($id, $user['id'], $user['role']);
        $this->trips->delete($id);
        Web::redirect($user['role'] === 'admin' ? '/admin/trajets' : '/', 'Le trajet a été supprimé.');
    }

    /** @return array<string,mixed> */
    private function editable(int $id, int $userId, string $role): array
    {
        $trip = $this->trips->find($id);
        if ($trip === false) {
            Web::abort(404, 'Trajet introuvable.');
        }
        if ($role !== 'admin' && (int) $trip['auteur_id'] !== $userId) {
            Web::abort(403, 'Seul l’auteur peut modifier ou supprimer ce trajet.');
        }
        return $trip;
    }

    /** @param array<string,mixed> $trip */
    private function showForm(array $trip, bool $edit, ?string $error = null): void
    {
        Web::view('trip-form', [
            'trip' => $trip,
            'edit' => $edit,
            'error' => $error,
            'agencies' => $this->agencies->all(),
        ]);
    }
}
