<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Repositories\EquipmentRepository;
use App\Repositories\RentalRepository;
use App\Services\RentalService;

final class RentalController extends AbstractController
{
    private EquipmentRepository $equipment;
    private RentalRepository $rentals;
    private RentalService $service;

    public function __construct()
    {
        parent::__construct();
        $this->equipment = new EquipmentRepository();
        $this->rentals   = new RentalRepository();
        $this->service   = new RentalService($this->rentals, $this->equipment);
    }

    public function newForm(array $params = []): void
    {
        $equipmentId = (int) ($this->request->input('equipment_id') ?? 0);
        $eq = $this->equipment->findById($equipmentId);
        if ($eq === null) {
            $this->render('errors/404', [], 404);
            return;
        }
        $this->render('rentals/new', ['eq' => $eq, 'errors' => []]);
    }

    public function create(array $params = []): void
    {
        $userId = (int) Session::userId();
        $result = $this->service->rent(
            userId:      $userId,
            equipmentId: (int) $this->request->input('equipment_id', '0'),
            quantity:    (int) $this->request->input('quantity', '1'),
            startDate:   (string) $this->request->input('start_date', ''),
            endDate:     (string) $this->request->input('end_date', ''),
        );
        if (!$result['ok']) {
            Session::flash('error', $result['error'] ?? 'Błąd wypożyczenia.');
            $this->redirect('/equipment');
            return;
        }
        Session::flash('success', 'Wypożyczenie założone.');
        $this->redirect('/rentals/mine');
    }

    public function mine(array $params = []): void
    {
        $list = $this->rentals->findByUser((int) Session::userId());
        $this->render('rentals/mine', ['rentals' => $list]);
    }

    public function returnRental(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $rental = $this->rentals->findById($id);
        if ($rental === null) {
            $this->render('errors/404', [], 404);
            return;
        }
        // Klient może zwrócić własne; admin/pracownik dowolne.
        $role = Session::userRole();
        if ($role === 'klient' && $rental->userId !== (int) Session::userId()) {
            $this->render('errors/403', [], 403);
            return;
        }
        $res = $this->service->returnRental($id);
        Session::flash($res['ok'] ? 'success' : 'error', $res['ok'] ? 'Sprzęt zwrócony.' : ($res['error'] ?? 'Błąd zwrotu.'));
        $this->redirect($role === 'klient' ? '/rentals/mine' : '/admin/rentals');
    }

    public function adminIndex(array $params = []): void
    {
        $this->render('admin/rentals/index', [
            'rentals' => $this->rentals->findAllWithUser(),
        ]);
    }
}
