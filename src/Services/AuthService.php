<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use InvalidArgumentException;

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

    /** @return string[] */
    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Nieprawidlowy email.';
        }
        if (empty($data['password']) || strlen($data['password']) < 6) {
            $errors[] = 'Haslo musi miec min. 6 znakow.';
        }
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $errors[] = 'Imie i nazwisko sa wymagane.';
        }
        return $errors;
    }
}
