<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\View;

/**
 * Bazowa klasa kontrolera - renderowanie widoku + skroty na odpowiedzi.
 */
abstract class AbstractController
{
    protected View $view;
    protected Request $request;

    public function __construct(?View $view = null, ?Request $request = null)
    {
        $this->view    = $view    ?? new View(dirname(__DIR__, 2) . '/views');
        $this->request = $request ?? new Request();
    }

    protected function render(string $template, array $data = [], int $status = 200): void
    {
        Response::html($this->view->render($template, $data), $status);
    }

    protected function json(array $data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function redirect(string $url): void
    {
        Response::redirect($url);
    }
}
