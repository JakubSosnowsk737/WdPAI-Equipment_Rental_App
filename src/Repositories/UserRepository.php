<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

final class UserRepository extends AbstractRepository
{
    public function findById(int $id): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE id = :id',
            ['id' => $id]
        );
        return $row ? User::fromRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne(
            'SELECT * FROM users WHERE email = :email',
            ['email' => $email]
        );
        return $row ? User::fromRow($row) : null;
    }

    public function create(User $user): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash, first_name, last_name, role)
             VALUES (:email, :pwd, :fn, :ln, :role) RETURNING id'
        );
        $stmt->execute([
            'email' => $user->email,
            'pwd'   => $user->passwordHash,
            'fn'    => $user->firstName,
            'ln'    => $user->lastName,
            'role'  => $user->role,
        ]);
        return (int) $stmt->fetchColumn();
    }

    /** @return User[] */
    public function findAll(): array
    {
        $rows = $this->fetchAll('SELECT * FROM users ORDER BY id');
        return array_map([User::class, 'fromRow'], $rows);
    }

    public function updateRole(int $userId, string $role): void
    {
        $this->execute(
            'UPDATE users SET role = :role WHERE id = :id',
            ['role' => $role, 'id' => $userId]
        );
    }

    public function delete(int $userId): void
    {
        $this->execute('DELETE FROM users WHERE id = :id', ['id' => $userId]);
    }
}
