<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

// Nie 'final' - umozliwia mockowanie w testach jednostkowych (PHPUnit).
class UserRepository extends AbstractRepository
{
    // Jawna lista kolumn - pobieramy tylko to, czego potrzebuje model.
    // Unikamy SELECT * (mniej danych, brak przypadkowego wycieku nowych kolumn).
    private const COLUMNS = 'id, email, password_hash, first_name, last_name, role, created_at';

    private static ?UserRepository $instance = null;

    /**
     * Jedna instancja repozytorium w cyklu zadania -> spojnosc i kontrola.
     * Konstruktor pozostaje publiczny (z AbstractRepository), aby mozna bylo
     * wstrzyknac mock PDO w testach (DI), ale produkcyjnie uzywamy getInstance().
     */
    public static function getInstance(): UserRepository
    {
        return self::$instance ??= new self();
    }

    public function findById(int $id): ?User
    {
        $row = $this->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE id = :id',
            ['id' => $id]
        );
        return $row ? User::fromRow($row) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $row = $this->fetchOne(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE email = :email',
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
        $rows = $this->fetchAll('SELECT ' . self::COLUMNS . ' FROM users ORDER BY id');
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
