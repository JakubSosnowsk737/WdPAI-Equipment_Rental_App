<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\LoginThrottle;

final class AuthController extends AbstractController
{
    private AuthService $auth;
    private LoginThrottle $throttle;

    public function __construct()
    {
        parent::__construct();
        $this->auth     = new AuthService(UserRepository::getInstance());
        $this->throttle = new LoginThrottle(new LoginAttemptRepository());
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
        $ip       = $this->request->ip();

        // Limit prob logowania (brute-force). Generyczny komunikat - nie zdradza
        // czy to blokada czy bledne haslo wobec konkretnego konta.
        if ($this->throttle->isLocked($email, $ip)) {
            $this->render('auth/login', [
                'error' => 'Zbyt wiele prob logowania. Sprobuj ponownie za kilka minut.',
            ], 429);
            return;
        }

        $user = $this->auth->verify($email, $password);
        if ($user === null) {
            $this->throttle->registerFailure($email, $ip);
            // Generyczny komunikat - nie zdradzamy, czy email istnieje.
            $this->render('auth/login', ['error' => 'Email lub haslo jest niepoprawne.'], 401);
            return;
        }

        $this->throttle->registerSuccess($email, $ip);
        Session::login($user);
        $this->redirect('/');
    }

    public function logout(array $params = []): void
    {
        Session::logout();
        $this->redirect('/');
    }
}
