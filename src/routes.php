<?php
declare(strict_types=1);

/** @var App\Core\Router $router */

use App\Controllers\AuthController;
use App\Controllers\HomeController;

$router->get('/',         [HomeController::class, 'index']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/logout',   [AuthController::class, 'logout']);
