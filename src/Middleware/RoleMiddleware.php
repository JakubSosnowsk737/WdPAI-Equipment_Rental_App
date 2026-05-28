<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\ErrorHandler;
use App\Core\Request;
use App\Core\Session;

/**
 * Wymaga okreslonej roli - inaczej 403.
 */
final class RoleMiddleware
{
    /** @param string[] $allowed */
    public function __construct(private array $allowed) {}

    public function __invoke(Request $request): void
    {
        $role = Session::userRole();
        if ($role === null || !in_array($role, $this->allowed, true)) {
            ErrorHandler::renderErrorPage(403);
            exit;
        }
    }
}
