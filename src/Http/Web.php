<?php
declare(strict_types=1);

namespace App\Http;

/** Gestion des vues, de la session et des réponses HTTP. */
final class Web
{
    /** @param array<string,mixed> $data */
    public static function view(string $page, array $data = []): void
    {
        $user = self::user();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        extract($data, EXTR_SKIP);
        require dirname(__DIR__, 2) . '/views/layout.php';
    }

    /** @return array{id:int,nom:string,prenom:string,email:string,telephone:string,role:string}|null */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /** @return array{id:int,nom:string,prenom:string,email:string,telephone:string,role:string} */
    public static function requireUser(bool $admin = false): array
    {
        $user = self::user();
        if ($user === null) {
            self::redirect('/connexion');
        }
        if ($admin && $user['role'] !== 'admin') {
            self::abort(403, 'Cette page est réservée à l’administration.');
        }
        return $user;
    }

    public static function redirect(string $path, ?string $message = null): never
    {
        if ($message !== null) {
            $_SESSION['flash'] = $message;
        }
        header('Location: ' . $path, true, 303);
        exit;
    }

    public static function abort(int $status, string $message): never
    {
        http_response_code($status);
        self::view('error', ['message' => $message]);
        exit;
    }

    public static function token(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function checkToken(): void
    {
        $token = $_POST['csrf'] ?? null;
        if (!is_string($token) || !hash_equals(self::token(), $token)) {
            self::abort(403, 'Formulaire expiré. Rechargez la page et réessayez.');
        }
    }

    public static function id(): int
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            self::abort(404, 'Élément introuvable.');
        }
        return $id;
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
