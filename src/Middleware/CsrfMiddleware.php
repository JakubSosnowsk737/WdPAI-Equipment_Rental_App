<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\ErrorHandler;
use App\Core\Request;

/**
 * Weryfikuje token CSRF dla zadan modyfikujacych stan (POST/PUT/DELETE).
 * Brak lub niezgodny token konczy sie odpowiedzia 403.
 */
final class CsrfMiddleware
{
    public function __invoke(Request $request): void
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input(Csrf::FIELD);
            if (!Csrf::verify($token)) {
                ErrorHandler::renderErrorPage(403);
                exit;
            }
        }
    }
}
