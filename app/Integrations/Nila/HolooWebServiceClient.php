<?php

namespace App\Integrations\Nila;

use Illuminate\Support\Facades\Http;
use RuntimeException;

final class HolooWebServiceClient
{
    private ?string $authorization = null;

    public function login(): void
    {
        $baseUrl = $this->baseUrl();
        $username = trim((string) config('nila.username'));
        $password = (string) config('nila.password');
        $database = trim((string) config('nila.database'));

        if ($username === '' || $password === '' || $database === '') {
            throw new RuntimeException(
                'Holoo credentials are incomplete. Set NILA_USERNAME, NILA_PASSWORD and NILA_DATABASE.'
            );
        }

        $response = Http::acceptJson()
            ->asJson()
            ->connectTimeout(min(5, $this->timeout()))
            ->timeout($this->timeout())
            ->post($baseUrl . '/Login', [
                'userinfo' => [
                    'username' => $username,
                    // The supplied Holoo manual documents Base64-encoded userpass.
                    'userpass' => base64_encode($password),
                    'dbname' => $database,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Holoo login failed with HTTP status ' . $response->status() . '. Check the service address and credentials.'
            );
        }

        $payload = $response->json();
        $login = is_array($payload) ? $this->value($payload, 'Login') : null;

        if (! is_array($login)) {
            throw new RuntimeException('Holoo login returned an unexpected response format.');
        }

        $state = strtolower(trim((string) $this->value($login, 'State', 'false')));
        if (! in_array($state, ['true', '1', 'yes'], true)) {
            $errorCode = trim((string) $this->value($login, 'ErrorCode', 'unknown'));
            throw new RuntimeException('Holoo rejected the login (error code: ' . $errorCode . ').');
        }

        $token = trim((string) $this->value($login, 'Token', ''));
        if ($token === '') {
            throw new RuntimeException('Holoo login succeeded without returning an authorization token.');
        }

        $this->authorization = preg_match('/^Bearer\s/i', $token) === 1
            ? $token
            : 'Bearer ' . $token;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = []): array
    {
        if ($this->authorization === null) {
            $this->login();
        }

        $response = $this->sendGet($path, $query);

        if (in_array($response->status(), [401, 403], true)) {
            $this->login();
            $response = $this->sendGet($path, $query);
        }

        if (! $response->successful()) {
            throw new RuntimeException(
                'Holoo request failed for ' . $path . ' with HTTP status ' . $response->status() . '.'
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('Holoo returned invalid JSON for ' . $path . '.');
        }

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMainGroups(): array
    {
        return $this->listFrom($this->get('MainGroup'), 'mainGroup');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSideGroups(): array
    {
        return $this->listFrom($this->get('SideGroup'), 'sideGroup');
    }

    /**
     * Holoo documents paging as /Product/{page}/{pageSize}.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProductsPage(int $page, int $pageSize): array
    {
        $payload = $this->get(
            'Product/' . max(1, $page) . '/' . max(1, $pageSize),
            ['getimage' => 'false', 'fa' => 'false']
        );

        return $this->listFrom($payload, 'product');
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<int, array<string, mixed>>
     */
    private function listFrom(array $payload, string $key): array
    {
        $valueFound = false;
        $value = null;

        foreach ($payload as $candidate => $candidateValue) {
            if (is_string($candidate) && strcasecmp($candidate, $key) === 0) {
                $valueFound = true;
                $value = $candidateValue;
                break;
            }
        }

        if (! $valueFound) {
            if ($payload === []) {
                return [];
            }

            throw new RuntimeException(
                'Holoo response did not contain the expected ' . $key . ' list.'
            );
        }

        if (! is_array($value)) {
            throw new RuntimeException('Holoo returned an invalid ' . $key . ' list.');
        }

        if (array_is_list($value)) {
            return array_values(array_filter($value, 'is_array'));
        }

        // Some service installations may return a single record as an object.
        return $value === [] ? [] : [$value];
    }

    private function sendGet(string $path, array $query)
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders(['Authorization' => (string) $this->authorization])
            ->connectTimeout(min(5, $this->timeout()))
            ->timeout($this->timeout())
            ->get($this->baseUrl() . '/' . ltrim($path, '/'), $query);
    }

    private function baseUrl(): string
    {
        $url = rtrim(trim((string) config('nila.base_url')), '/');

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException(
                'Holoo service URL is not configured. Set NILA_BASE_URL to the full /TncHoloo/api address.'
            );
        }

        return $url;
    }

    private function timeout(): int
    {
        return max(3, (int) config('nila.timeout', 20));
    }

    private function value(array $array, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach ($array as $candidate => $value) {
            if (is_string($candidate) && strcasecmp($candidate, $key) === 0) {
                return $value;
            }
        }

        return $default;
    }
}
