<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Wymaga zalogowania - inaczej przekierowanie na /login.
 */
final class AuthMiddleware
{
    public function __invoke(Request $request): void
    {
        if (!Session::isAuthenticated()) {
            Session::flash('error', 'Musisz sie zalogowac.');
            Response::redirect('/login');
        }
    }
}
