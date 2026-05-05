<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\CategoryRepository;
use App\Repositories\EquipmentRepository;

final class EquipmentController extends AbstractController
{
    private EquipmentRepository $equipment;
    private CategoryRepository $categories;

    public function __construct()
    {
        parent::__construct();
        $this->equipment  = new EquipmentRepository();
        $this->categories = new CategoryRepository();
    }

    public function index(array $params = []): void
    {
        $this->render('equipment/index', [
            'items'      => $this->equipment->findAllWithCategory(),
            'categories' => $this->categories->findAll(),
        ]);
    }

    public function show(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $eq = $this->equipment->findById($id);
        if ($eq === null) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }
        $this->render('equipment/show', ['eq' => $eq]);
    }
}
