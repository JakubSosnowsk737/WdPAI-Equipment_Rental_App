<?php
declare(strict_types=1);

/** @var App\Core\Router $router */

// Na razie pusto - kolejne kontrolery beda dodawane.
$router->get('/', [App\Controllers\HomeController::class, 'index']);
