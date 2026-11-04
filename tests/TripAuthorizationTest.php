<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/** Vérifie les droits de modification et de suppression sur le serveur local. */
final class TripAuthorizationTest extends TestCase
{
    public function testEmployeeCannotChangeAnotherAuthorsTrip(): void
    {
        $base = getenv('TEST_HTTP_URL');
        if (!$base) {
            self::markTestSkipped('Démarrez la démonstration locale et définissez TEST_HTTP_URL.');
        }

        [$adminCookie, $adminToken] = $this->login($base, 'admin@example.test', 'Klaxon-Admin-2026!');
        [$employeeCookie, $employeeToken] = $this->login($base, 'employe@example.test', 'Klaxon-Employe-2026!');
        [, $form] = $this->request($base, '/trajets/nouveau', $adminCookie);
        preg_match_all('/<option value="(\d+)"/', $form, $agencies);
        $ids = array_values(array_unique($agencies[1]));
        self::assertGreaterThanOrEqual(2, count($ids));

        $departure = new DateTimeImmutable('+40 days');
        $input = [
            'agence_depart_id' => $ids[0],
            'agence_arrivee_id' => $ids[1],
            'depart' => $departure->format('Y-m-d') . 'T08:37',
            'arrivee' => $departure->format('Y-m-d') . 'T10:37',
            'places_totales' => 4,
            'places_disponibles' => 2,
        ];

        [$status] = $this->request($base, '/trajets/nouveau', $adminCookie, ['csrf' => $adminToken] + $input);
        self::assertSame(303, $status);
        [, $list] = $this->request($base, '/admin/trajets', $adminCookie);
        preg_match_all('/<tr>.*?<\/tr>/s', $list, $rows);
        $tripId = null;
        foreach ($rows[0] as $row) {
            if (str_contains($row, $departure->format('d/m/Y')) && str_contains($row, '>08:37<')
                && preg_match('/trajets\/modifier\?id=(\d+)/', $row, $match)) {
                $tripId = $match[1];
            }
        }
        self::assertNotNull($tripId, 'Le trajet de test doit apparaître dans la liste administrateur.');

        try {
            [$status] = $this->request($base, "/trajets/modifier?id=$tripId", $employeeCookie);
            self::assertSame(403, $status);
            [$status] = $this->request($base, "/trajets/modifier?id=$tripId", $employeeCookie,
                ['csrf' => $employeeToken, 'places_disponibles' => 1] + $input);
            self::assertSame(403, $status);
            [$status] = $this->request($base, "/trajets/supprimer?id=$tripId", $employeeCookie, ['csrf' => $employeeToken]);
            self::assertSame(403, $status);
            [$status, $form] = $this->request($base, "/trajets/modifier?id=$tripId", $adminCookie);
            self::assertSame(200, $status);
            self::assertMatchesRegularExpression('/name="places_disponibles"[^>]*value="2"/', $form);
        } finally {
            $this->request($base, "/trajets/supprimer?id=$tripId", $adminCookie, ['csrf' => $adminToken]);
        }
    }

    /** @return array{string,string} */
    private function login(string $base, string $email, string $password): array
    {
        [$status, $form, $cookie] = $this->request($base, '/connexion');
        self::assertSame(200, $status);
        [$status, , $authenticatedCookie] = $this->request($base, '/connexion', $cookie,
            ['csrf' => $this->token($form), 'email' => $email, 'password' => $password]);
        self::assertSame(303, $status);
        [$status, $page] = $this->request($base, '/admin', $authenticatedCookie);
        if ($status === 403) {
            [$status, $page] = $this->request($base, '/', $authenticatedCookie);
        }
        self::assertSame(200, $status);
        return [$authenticatedCookie, $this->token($page)];
    }

    private function token(string $page): string
    {
        self::assertSame(1, preg_match('/name="csrf" value="([^"]+)"/', $page, $match));
        return $match[1];
    }

    /** @param array<string,int|string>|null $data @return array{int,string,string} */
    private function request(string $base, string $path, string $cookie = '', ?array $data = null): array
    {
        $headers = $cookie === '' ? [] : ["Cookie: $cookie"];
        $options = ['ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5];
        if ($data !== null) {
            $options['method'] = 'POST';
            $options['content'] = http_build_query($data);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        }
        $options['header'] = $headers;
        $response = file_get_contents(rtrim($base, '/') . $path, false, stream_context_create(['http' => $options]));
        self::assertNotFalse($response);
        $status = (int) explode(' ', $http_response_header[0])[1];
        foreach ($http_response_header as $header) {
            if (preg_match('/^Set-Cookie: ([^;]+)/i', $header, $match)) {
                $cookie = $match[1];
            }
        }
        return [$status, $response, $cookie];
    }
}
