<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Wygodna fasada dla $_SESSION.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $name = Config::get('SESSION_NAME', 'wpro_sid');
            session_name($name);
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }
    }

    public static function login(User $user): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user->id;
        $_SESSION['user_role'] = $user->role;
        $_SESSION['user_name'] = $user->fullName();
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    public static function userId(): ?int
    {
        self::start();
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function userRole(): ?string
    {
        self::start();
        return $_SESSION['user_role'] ?? null;
    }

    public static function userName(): ?string
    {
        self::start();
        return $_SESSION['user_name'] ?? null;
    }

    public static function isAuthenticated(): bool
    {
        return self::userId() !== null;
    }

    public static function flash(string $key, ?string $value = null): ?string
    {
        self::start();
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}
