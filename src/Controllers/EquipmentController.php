<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\Equipment;
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

    public function createForm(array $params = []): void
    {
        $this->render('admin/equipment/form', [
            'eq'         => null,
            'categories' => $this->categories->findAll(),
            'errors'     => [],
        ]);
    }

    public function create(array $params = []): void
    {
        $data = $this->request->all();
        $errors = $this->validate($data);
        if ($errors !== []) {
            $this->render('admin/equipment/form', [
                'eq'         => null,
                'categories' => $this->categories->findAll(),
                'errors'     => $errors,
            ], 422);
            return;
        }
        $total = (int) $data['total_quantity'];
        $eq = new Equipment(
            id:                null,
            categoryId:        (int) $data['category_id'],
            name:              (string) $data['name'],
            description:       (string) ($data['description'] ?? ''),
            dailyRate:         (float) $data['daily_rate'],
            totalQuantity:     $total,
            availableQuantity: $total,
        );
        $this->equipment->create($eq);
        Session::flash('success', 'Dodano sprzet.');
        $this->redirect('/admin/equipment');
    }

    public function adminIndex(array $params = []): void
    {
        $this->render('admin/equipment/index', [
            'items' => $this->equipment->findAllWithCategory(),
        ]);
    }

    /** @return string[] */
    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['name']))           { $errors[] = 'Nazwa wymagana.'; }
        if (empty($data['category_id']))    { $errors[] = 'Kategoria wymagana.'; }
        if (!isset($data['daily_rate']) || (float) $data['daily_rate'] < 0) {
            $errors[] = 'Stawka dzienna musi byc nieujemna.';
        }
        if (!isset($data['total_quantity']) || (int) $data['total_quantity'] < 1) {
            $errors[] = 'Ilosc musi byc dodatnia.';
        }
        return $errors;
    }
}
