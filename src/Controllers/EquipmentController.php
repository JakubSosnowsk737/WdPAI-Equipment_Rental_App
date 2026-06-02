<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Models\Equipment;
use App\Repositories\CategoryRepository;
use App\Repositories\EquipmentImageRepository;
use App\Repositories\EquipmentRepository;

final class EquipmentController extends AbstractController
{
    private EquipmentRepository $equipment;
    private CategoryRepository $categories;
    private EquipmentImageRepository $images;

    public function __construct()
    {
        parent::__construct();
        $this->equipment  = new EquipmentRepository();
        $this->categories = new CategoryRepository();
        $this->images     = new EquipmentImageRepository();
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
            $this->render('errors/404', [], 404);
            return;
        }
        $this->render('equipment/show', [
            'eq'      => $eq,
            'images'  => $this->images->listForEquipment($id),
            'related' => $this->equipment->findRelated($eq->categoryId, $id, 3),
        ]);
    }

    public function uploadImage(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Blad uploadu pliku.');
            $this->redirect('/admin/equipment/' . $id . '/edit');
            return;
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'], true)) {
            Session::flash('error', 'Niedozwolony format obrazka.');
            $this->redirect('/admin/equipment/' . $id . '/edit');
            return;
        }
        $dir = dirname(__DIR__, 2) . '/public/uploads';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $name = 'eq_' . $id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $name);
        $this->images->add($id, '/uploads/' . $name);
        Session::flash('success', 'Dodano obraz.');
        $this->redirect('/admin/equipment/' . $id . '/edit');
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

    public function apiSearch(array $params = []): void
    {
        $q   = $this->request->input('q');
        $cat = $this->request->input('category_id');
        $items = $this->equipment->search($q, $cat !== null && $cat !== '' ? (int) $cat : null);
        $out = array_map(static fn($e) => [
            'id'                 => $e->id,
            'name'               => $e->name,
            'description'        => $e->description,
            'category_name'      => $e->categoryName,
            'daily_rate'         => $e->dailyRate,
            'total_quantity'     => $e->totalQuantity,
            'available_quantity' => $e->availableQuantity,
        ], $items);
        $this->json(['items' => $out]);
    }

    public function adminIndex(array $params = []): void
    {
        $this->render('admin/equipment/index', [
            'items' => $this->equipment->findAllWithCategory(),
        ]);
    }

    public function editForm(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $eq = $this->equipment->findById($id);
        if ($eq === null) {
            $this->render('errors/404', [], 404);
            return;
        }
        $this->render('admin/equipment/form', [
            'eq'         => $eq,
            'categories' => $this->categories->findAll(),
            'errors'     => [],
        ]);
    }

    public function update(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $data = $this->request->all();
        $errors = $this->validate($data);
        if ($errors !== []) {
            $this->render('admin/equipment/form', [
                'eq'         => $this->equipment->findById($id),
                'categories' => $this->categories->findAll(),
                'errors'     => $errors,
            ], 422);
            return;
        }
        $total = (int) $data['total_quantity'];
        $existing = $this->equipment->findById($id);
        $avail = max(0, $total - ($existing ? $existing->totalQuantity - $existing->availableQuantity : 0));
        $eq = new Equipment(
            id:                $id,
            categoryId:        (int) $data['category_id'],
            name:              (string) $data['name'],
            description:       (string) ($data['description'] ?? ''),
            dailyRate:         (float) $data['daily_rate'],
            totalQuantity:     $total,
            availableQuantity: $avail,
        );
        $this->equipment->update($id, $eq);
        Session::flash('success', 'Zaktualizowano sprzet.');
        $this->redirect('/admin/equipment');
    }

    public function delete(array $params): void
    {
        $id = (int) ($params['id'] ?? 0);
        $this->equipment->delete($id);
        Session::flash('success', 'Usunieto sprzet.');
        $this->redirect('/admin/equipment');
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
