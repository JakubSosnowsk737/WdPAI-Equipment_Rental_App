<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
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
        Session::flash('success', 'Konto utworzone, mozesz sie zalogowac.');
        $this->redirect('/login');
    }

    public function showLogin(array $params = []): void
    {
        $this->render('auth/login', ['error' => null]);
    }

    public function login(array $params = []): void
    {
        $email    = (string) $this->request->input('email', '');
        $password = (string) $this->request->input('password', '');

        $user = $this->auth->verify($email, $password);
        if ($user === null) {
            $this->render('auth/login', ['error' => 'Bledne dane logowania.'], 401);
            return;
        }
        Session::login($user);
        $this->redirect('/');
    }

    public function logout(array $params = []): void
    {
        Session::logout();
        $this->redirect('/');
    }
}
