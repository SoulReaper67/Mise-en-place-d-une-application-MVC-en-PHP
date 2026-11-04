<?php
declare(strict_types=1);

use App\Controller\AdminController;
use App\Controller\AuthController;
use App\Controller\TripController;
use App\Http\Router;
use App\Http\Web;
use App\Model\AgencyRepository;
use App\Model\EmployeeRepository;
use App\Model\TripRepository;

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('Europe/Paris');
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();

try {
    $pdo = require dirname(__DIR__) . '/config/database.php';
    $agencies = new AgencyRepository($pdo);
    $employees = new EmployeeRepository($pdo);
    $auth = new AuthController($employees);
    $trips = new TripController(new TripRepository($pdo), $agencies);
    $admin = new AdminController($agencies, $employees);
    $router = new Router();
    require dirname(__DIR__) . '/config/routes.php';
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', is_string($path) ? $path : '/');
} catch (Throwable $exception) {
    error_log($exception->__toString());
    Web::abort(500, 'Une erreur est survenue. Veuillez réessayer plus tard.');
}
