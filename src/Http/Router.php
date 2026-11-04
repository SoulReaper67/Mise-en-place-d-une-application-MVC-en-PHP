<?php
declare(strict_types=1);

namespace App\Http;

use Closure;

/** Associe une méthode HTTP et un chemin à une action. */
final class Router
{
    /** @var array<string,array<string,Closure>> */
    private array $routes = [];

    public function add(string $method, string $path, Closure $action): void
    {
        $this->routes[$path][$method] = $action;
    }

    public function dispatch(string $method, string $path): void
    {
        $action = $this->routes[$path][$method] ?? null;
        if ($action === null) {
            if (isset($this->routes[$path])) {
                header('Allow: ' . implode(', ', array_keys($this->routes[$path])));
                Web::abort(405, 'Méthode non autorisée.');
            }
            Web::abort(404, 'Page introuvable.');
        }
        $action();
    }
}
