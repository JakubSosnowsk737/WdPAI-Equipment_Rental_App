<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Bardzo proste renderowanie widokow PHP (bez frameworka).
 */
final class View
{
    public function __construct(private string $viewsDir) {}

    public function render(string $template, array $data = []): string
    {
        $path = $this->viewsDir . DIRECTORY_SEPARATOR
              . str_replace('/', DIRECTORY_SEPARATOR, $template) . '.php';
        if (!is_file($path)) {
            throw new RuntimeException("Brak widoku: $template");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}
