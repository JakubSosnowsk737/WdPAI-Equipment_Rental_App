<?php
declare(strict_types=1);

namespace App\Models;

final class User
{
    public const ROLE_ADMIN     = 'admin';
    public const ROLE_EMPLOYEE  = 'pracownik';
    public const ROLE_CLIENT    = 'klient';

    public function __construct(
        public readonly ?int $id,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $role,
        public readonly ?string $createdAt = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:           isset($row['id']) ? (int) $row['id'] : null,
            email:        (string) $row['email'],
            passwordHash: (string) $row['password_hash'],
            firstName:    (string) $row['first_name'],
            lastName:     (string) $row['last_name'],
            role:         (string) $row['role'],
            createdAt:    $row['created_at'] ?? null,
        );
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }
}
