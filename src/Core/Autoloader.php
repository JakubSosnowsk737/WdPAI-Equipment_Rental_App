<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Minimalny autoloader zgodny z PSR-4 (prefix "App\\" -> katalog "src/").
 */
final class Autoloader
{
    public static function register(string $baseDir): void
    {
        spl_autoload_register(static function (string $class) use ($baseDir): void {
            $prefix = 'App\\';
            if (!str_starts_with($class, $prefix)) {
                return;
            }
            $relative = substr($class, strlen($prefix));
            $path = $baseDir . DIRECTORY_SEPARATOR
                  . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            if (is_file($path)) {
                require $path;
            }
        });
    }
}
