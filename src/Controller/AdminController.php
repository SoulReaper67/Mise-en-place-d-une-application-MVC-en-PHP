<?php
declare(strict_types=1);

namespace App\Controller;

use App\Http\Web;
use App\Model\AgencyRepository;
use App\Model\EmployeeRepository;
use PDOException;

/** Administration des agences et lecture du référentiel RH. */
final class AdminController
{
    public function __construct(private AgencyRepository $agencies, private EmployeeRepository $employees) {}

    public function index(): void
    {
        Web::requireUser(true);
        Web::view('admin');
    }

    public function employees(): void
    {
        Web::requireUser(true);
        Web::view('employees', ['employees' => $this->employees->all()]);
    }

    public function agencies(): void
    {
        Web::requireUser(true);
        Web::view('agencies', ['agencies' => $this->agencies->all()]);
    }

    public function form(bool $edit = false): void
    {
        Web::requireUser(true);
        $agency = $edit ? $this->find(Web::id()) : [];
        Web::view('agency-form', ['agency' => $agency, 'edit' => $edit]);
    }

    public function save(bool $edit = false): void
    {
        Web::requireUser(true);
        Web::checkToken();
        $id = $edit ? $this->find(Web::id())['id'] : null;
        $input = $_POST['nom'] ?? null;
        $name = is_string($input) ? trim($input) : '';
        if ($name === '' || preg_match('/^.{1,100}$/uD', $name) !== 1) {
            $this->invalid($id, $name, 'Le nom doit contenir entre 1 et 100 caractères.');
            return;
        }
        try {
            $this->agencies->save($id, $name);
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }
            $this->invalid($id, $name, 'Une agence porte déjà ce nom.');
            return;
        }
        Web::redirect('/admin/agences', $edit ? 'L’agence a été modifiée.' : 'L’agence a été créée.');
    }

    public function delete(): void
    {
        Web::requireUser(true);
        Web::checkToken();
        $id = $this->find(Web::id())['id'];
        try {
            $this->agencies->delete($id);
        } catch (PDOException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }
            Web::redirect('/admin/agences', 'Cette agence est associée à un trajet et ne peut pas être supprimée.');
        }
        Web::redirect('/admin/agences', 'L’agence a été supprimée.');
    }

    /** @return array{id:int,nom:string} */
    private function find(int $id): array
    {
        $agency = $this->agencies->find($id);
        if ($agency === false) {
            Web::abort(404, 'Agence introuvable.');
        }
        return $agency;
    }

    private function invalid(?int $id, string $name, string $error): void
    {
        http_response_code(422);
        Web::view('agency-form', ['agency' => ['id' => $id, 'nom' => $name], 'edit' => $id !== null, 'error' => $error]);
    }
}
