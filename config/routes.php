<?php
declare(strict_types=1);

/** @var App\Http\Router $router */
/** @var App\Controller\TripController $trips */
/** @var App\Controller\AuthController $auth */
/** @var App\Controller\AdminController $admin */

$router->add('GET', '/', $trips->index(...));
$router->add('GET', '/connexion', $auth->form(...));
$router->add('POST', '/connexion', $auth->login(...));
$router->add('POST', '/deconnexion', $auth->logout(...));
$router->add('GET', '/trajets/nouveau', $trips->form(...));
$router->add('POST', '/trajets/nouveau', $trips->save(...));
$router->add('GET', '/trajets/modifier', fn () => $trips->form(true));
$router->add('POST', '/trajets/modifier', fn () => $trips->save(true));
$router->add('POST', '/trajets/supprimer', $trips->delete(...));
$router->add('GET', '/admin', $admin->index(...));
$router->add('GET', '/admin/utilisateurs', $admin->employees(...));
$router->add('GET', '/admin/trajets', $trips->adminIndex(...));
$router->add('GET', '/admin/agences', $admin->agencies(...));
$router->add('GET', '/admin/agences/nouvelle', $admin->form(...));
$router->add('POST', '/admin/agences/nouvelle', $admin->save(...));
$router->add('GET', '/admin/agences/modifier', fn () => $admin->form(true));
$router->add('POST', '/admin/agences/modifier', fn () => $admin->save(true));
$router->add('POST', '/admin/agences/supprimer', $admin->delete(...));
