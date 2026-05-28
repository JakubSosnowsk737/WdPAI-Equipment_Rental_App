<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * Globalne lapanie wyjatkow i bledow PHP.
 * Tryb debug pokazuje szczegoly, produkcja - tylko strone bledu.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        $debug = Config::get('APP_DEBUG', 'false') === 'true';

        set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });

        set_exception_handler(static function (Throwable $e) use ($debug) {
            error_log('[wpro] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            http_response_code(500);

            if ($debug) {
                header('Content-Type: text/plain; charset=UTF-8');
                echo 'Wyjatek: ', $e->getMessage(), "\n\n", $e->getTraceAsString();
                return;
            }
            self::renderErrorPage(500);
        });
    }

    public static function renderErrorPage(int $status): void
    {
        http_response_code($status);
        $file = dirname(__DIR__, 2) . "/views/errors/$status.php";
        if (is_file($file)) {
            require $file;
        } else {
            echo "$status";
        }
    }
}
