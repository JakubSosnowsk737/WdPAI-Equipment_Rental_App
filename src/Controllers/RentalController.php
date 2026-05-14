<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\EquipmentRepository;

final class RentalController extends AbstractController
{
    private EquipmentRepository $equipment;

    public function __construct()
    {
        parent::__construct();
        $this->equipment = new EquipmentRepository();
    }

    public function newForm(array $params = []): void
    {
        $equipmentId = (int) ($this->request->input('equipment_id') ?? 0);
        $eq = $this->equipment->findById($equipmentId);
        if ($eq === null) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $this->render('rentals/new', ['eq' => $eq, 'errors' => []]);
    }
}
