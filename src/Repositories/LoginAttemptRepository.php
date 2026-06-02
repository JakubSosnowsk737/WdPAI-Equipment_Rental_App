<?php
declare(strict_types=1);

namespace App\Repositories;

// Nie 'final' - umozliwia mockowanie w testach jednostkowych (PHPUnit).
class LoginAttemptRepository extends AbstractRepository
{
    /**
     * Zapisuje probe logowania do audytu. Nigdy nie zapisujemy hasla.
     */
    public function record(string $email, string $ip, bool $successful): void
    {
        $this->execute(
            'INSERT INTO login_attempts (email, ip_address, successful)
             VALUES (:email, :ip, :ok)',
            ['email' => $email, 'ip' => $ip, 'ok' => $successful ? 'true' : 'false']
        );
    }

    /**
     * Liczba nieudanych prob dla danego emaila/IP w ostatnich N minutach.
     */
    public function countRecentFailures(string $email, string $ip, int $minutes): int
    {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS c
             FROM login_attempts
             WHERE successful = FALSE
               AND (email = :email OR ip_address = :ip)
               AND created_at > NOW() - (:mins || ' minutes')::interval",
            ['email' => $email, 'ip' => $ip, 'mins' => $minutes]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * Po udanym logowaniu czyscimy nieudane proby dla emaila (reset licznika).
     */
    public function clearFailures(string $email): void
    {
        $this->execute(
            'DELETE FROM login_attempts WHERE email = :email AND successful = FALSE',
            ['email' => $email]
        );
    }
}
