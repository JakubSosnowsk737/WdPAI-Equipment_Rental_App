<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Ochrona przed CSRF (Cross-Site Request Forgery).
 * Token trzymany w sesji, wstrzykiwany do formularzy jako ukryte pole
 * i weryfikowany przy kazdym zadaniu modyfikujacym stan (POST).
 */
final class Csrf
{
    private const SESSION_KEY = '_csrf_token';
    public const FIELD = 'csrf_token';

    public static function token(): string
    {
        Session::start();
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /** Gotowe ukryte pole do wstawienia w formularzu. */
    public static function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES) . '">';
    }

    /**
     * Porownanie odporne na timing attack (hash_equals).
     */
    public static function verify(?string $token): bool
    {
        Session::start();
        $expected = $_SESSION[self::SESSION_KEY] ?? '';
        return is_string($token) && $token !== '' && hash_equals($expected, $token);
    }
}
