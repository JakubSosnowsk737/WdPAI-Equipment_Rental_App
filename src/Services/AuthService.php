<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Warstwa logiki uwierzytelniania - rejestracja, logowanie.
 * Trzymamy ja oddzielnie od kontrolera (SRP).
 */
final class AuthService
{
    public function __construct(private UserRepository $users) {}

    /**
     * @param array<string,string> $data
     * @return string[] lista bledow walidacji (pusta = sukces)
     */
    public function register(array $data): array
    {
        $errors = $this->validate($data);
        if ($errors !== []) {
            return $errors;
        }

        if ($this->users->findByEmail($data['email']) !== null) {
            return ['Uzytkownik o tym emailu juz istnieje.'];
        }

        $user = new User(
            id:           null,
            email:        $data['email'],
            passwordHash: password_hash($data['password'], PASSWORD_BCRYPT),
            firstName:    $data['first_name'],
            lastName:     $data['last_name'],
            role:         User::ROLE_CLIENT,
        );
        $this->users->create($user);

        return [];
    }

    public function verify(string $email, string $password): ?User
    {
        $user = $this->users->findByEmail($email);
        if ($user === null) {
            return null;
        }
        return password_verify($password, $user->passwordHash) ? $user : null;
    }

    // Limity dlugosci wejscia - ochrona przed nadmiernym obciazeniem (DoS)
    // i zgodne z ograniczeniami kolumn w bazie.
    private const MAX_EMAIL    = 150;
    private const MAX_NAME     = 80;
    private const MIN_PASSWORD = 8;
    private const MAX_PASSWORD = 200;

    /** @return string[] */
    private function validate(array $data): array
    {
        $errors = [];

        $email = (string) ($data['email'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Nieprawidlowy email.';
        } elseif (strlen($email) > self::MAX_EMAIL) {
            $errors[] = 'Email jest zbyt dlugi (max ' . self::MAX_EMAIL . ' znakow).';
        }

        $password = (string) ($data['password'] ?? '');
        if (strlen($password) < self::MIN_PASSWORD) {
            $errors[] = 'Haslo musi miec min. ' . self::MIN_PASSWORD . ' znakow.';
        } elseif (strlen($password) > self::MAX_PASSWORD) {
            $errors[] = 'Haslo jest zbyt dlugie.';
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            // Walidacja zlozonosci - co najmniej jedna litera i jedna cyfra.
            $errors[] = 'Haslo musi zawierac litere i cyfre.';
        }

        $firstName = (string) ($data['first_name'] ?? '');
        $lastName  = (string) ($data['last_name'] ?? '');
        if ($firstName === '' || $lastName === '') {
            $errors[] = 'Imie i nazwisko sa wymagane.';
        } elseif (strlen($firstName) > self::MAX_NAME || strlen($lastName) > self::MAX_NAME) {
            $errors[] = 'Imie/nazwisko sa zbyt dlugie (max ' . self::MAX_NAME . ' znakow).';
        }

        return $errors;
    }
}
