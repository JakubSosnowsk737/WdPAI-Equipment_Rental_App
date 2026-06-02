<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\LoginAttemptRepository;

/**
 * Ochrona przed brute-force: limit nieudanych prob logowania w oknie czasowym
 * oraz audyt prob (bez hasel). Po przekroczeniu progu logowanie jest blokowane.
 */
final class LoginThrottle
{
    private const MAX_FAILURES   = 5;   // dozwolone nieudane proby
    private const WINDOW_MINUTES = 15;  // okno czasowe blokady

    public function __construct(private LoginAttemptRepository $attempts) {}

    public function isLocked(string $email, string $ip): bool
    {
        return $this->attempts->countRecentFailures($email, $ip, self::WINDOW_MINUTES)
            >= self::MAX_FAILURES;
    }

    public function registerFailure(string $email, string $ip): void
    {
        $this->attempts->record($email, $ip, false);
        // Audyt do logu serwera - bez hasla.
        error_log(sprintf('[wpro][audit] Nieudane logowanie email=%s ip=%s', $email, $ip));
    }

    public function registerSuccess(string $email, string $ip): void
    {
        $this->attempts->record($email, $ip, true);
        $this->attempts->clearFailures($email);
    }
}
