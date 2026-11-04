<?php
declare(strict_types=1);

namespace App\Controller;

use App\Http\Web;
use App\Model\EmployeeRepository;

/** Ouvre ou ferme la session d’un employé. */
final class AuthController
{
    public function __construct(private EmployeeRepository $employees) {}

    public function form(): void
    {
        Web::view('login');
    }

    public function login(): void
    {
        Web::checkToken();
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $employee = is_string($email) ? $this->employees->findByEmail(trim($email)) : false;
        if ($employee === false || !is_string($password) || !password_verify($password, $employee['mot_de_passe'])) {
            http_response_code(422);
            Web::view('login', ['error' => 'Adresse email ou mot de passe incorrect.']);
            return;
        }
        session_regenerate_id(true);
        unset($employee['mot_de_passe'], $_SESSION['csrf']);
        $employee['id'] = (int) $employee['id'];
        $_SESSION['user'] = $employee;
        Web::redirect($employee['role'] === 'admin' ? '/admin' : '/');
    }

    public function logout(): void
    {
        Web::checkToken();
        $_SESSION = [];
        session_regenerate_id(true);
        Web::redirect('/');
    }
}
