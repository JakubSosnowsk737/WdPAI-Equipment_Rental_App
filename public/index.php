<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/bootstrap.php';

use App\Core\Request;
use App\Core\Router;
use App\Middleware\CsrfMiddleware;

$router = new Router();
// Globalna ochrona CSRF dla wszystkich zadan POST/PUT/PATCH/DELETE.
$router->addGlobalMiddleware(new CsrfMiddleware());
require dirname(__DIR__) . '/src/routes.php';

$router->dispatch(new Request());
