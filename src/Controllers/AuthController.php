<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Services\AuthService;

final class AuthController extends AbstractController
{
    private AuthService $auth;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthService(new UserRepository());
    }

    public function showRegister(array $params = []): void
    {
        $this->render('auth/register', ['errors' => []]);
    }

    public function register(array $params = []): void
    {
        $errors = $this->auth->register($this->request->all());
        if ($errors !== []) {
            $this->render('auth/register', ['errors' => $errors], 422);
            return;
        }
        $this->redirect('/login');
    }

    public function showLogin(array $params = []): void
    {
        $this->render('auth/login', ['error' => null]);
    }
}
