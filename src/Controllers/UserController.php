<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\User;
use App\Repositories\UserRepository;

final class UserController extends AbstractController
{
    private UserRepository $users;

    public function __construct()
    {
        parent::__construct();
        $this->users = UserRepository::getInstance();
    }

    public function index(array $params = []): void
    {
        $this->render('admin/users/index', ['users' => $this->users->findAll()]);
    }

    public function updateRole(array $params): void
    {
        $id   = (int) ($params['id'] ?? 0);
        $role = (string) $this->request->input('role', User::ROLE_CLIENT);
        if (in_array($role, [User::ROLE_ADMIN, User::ROLE_EMPLOYEE, User::ROLE_CLIENT], true)) {
            $this->users->updateRole($id, $role);
            Session::flash('success', 'Rola zaktualizowana.');
        }
        $this->redirect('/admin/users');
    }

    public function delete(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        if ($id !== Session::userId()) {
            $this->users->delete($id);
            Session::flash('success', 'Użytkownik usunięty.');
        }
        $this->redirect('/admin/users');
    }
}
