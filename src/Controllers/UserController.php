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

        // Admin nie może zmienić własnej roli (ryzyko utraty dostępu).
        if ($id === Session::userId()) {
            Session::flash('error', 'Nie możesz zmienić własnej roli.');
            $this->redirect('/admin/users');
            return;
        }
        if (!in_array($role, [User::ROLE_ADMIN, User::ROLE_EMPLOYEE, User::ROLE_CLIENT], true)) {
            Session::flash('error', 'Nieprawidłowa rola.');
            $this->redirect('/admin/users');
            return;
        }
        $this->users->updateRole($id, $role);
        Session::flash('success', 'Rola zaktualizowana.');
        $this->redirect('/admin/users');
    }

    public function delete(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);

        // Admin nie może usunąć własnego konta.
        if ($id === Session::userId()) {
            Session::flash('error', 'Nie możesz usunąć własnego konta.');
            $this->redirect('/admin/users');
            return;
        }
        if ($this->users->findById($id) === null) {
            Session::flash('error', 'Użytkownik nie istnieje.');
            $this->redirect('/admin/users');
            return;
        }
        // Nie można usunąć użytkownika powiązanego z wypożyczeniami (FK RESTRICT).
        if ($this->users->hasRentals($id)) {
            Session::flash('error', 'Nie można usunąć użytkownika powiązanego z wypożyczeniami.');
            $this->redirect('/admin/users');
            return;
        }
        $this->users->delete($id);
        Session::flash('success', 'Użytkownik usunięty.');
        $this->redirect('/admin/users');
    }
}
