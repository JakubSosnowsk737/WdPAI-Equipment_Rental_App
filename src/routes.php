<?php
declare(strict_types=1);

/** @var App\Core\Router $router */

use App\Controllers\AuthController;
use App\Controllers\EquipmentController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Models\User;

$auth      = new AuthMiddleware();
$adminOnly = [$auth, new RoleMiddleware([User::ROLE_ADMIN])];

$router->get('/',         [HomeController::class, 'index']);
$router->get('/equipment',           [EquipmentController::class, 'index']);
$router->get('/equipment/{id}',      [EquipmentController::class, 'show']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/logout',   [AuthController::class, 'logout']);

$router->get('/admin/users',                    [UserController::class, 'index'],      $adminOnly);
$router->post('/admin/users/{id}/role',         [UserController::class, 'updateRole'], $adminOnly);
$router->post('/admin/users/{id}/delete',       [UserController::class, 'delete'],     $adminOnly);

$router->get('/admin/equipment',                [EquipmentController::class, 'adminIndex'],   $adminOnly);
$router->get('/admin/equipment/new',            [EquipmentController::class, 'createForm'],   $adminOnly);
$router->post('/admin/equipment',               [EquipmentController::class, 'create'],       $adminOnly);
$router->get('/admin/equipment/{id}/edit',      [EquipmentController::class, 'editForm'],     $adminOnly);
$router->post('/admin/equipment/{id}',          [EquipmentController::class, 'update'],       $adminOnly);
$router->post('/admin/equipment/{id}/delete',   [EquipmentController::class, 'delete'],       $adminOnly);
