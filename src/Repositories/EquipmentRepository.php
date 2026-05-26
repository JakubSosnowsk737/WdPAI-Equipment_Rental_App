<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Equipment;

final class EquipmentRepository extends AbstractRepository
{
    /** @return Equipment[] */
    public function findAllWithCategory(): array
    {
        $rows = $this->fetchAll(
            'SELECT e.*, c.name AS category_name
             FROM equipment e
             JOIN categories c ON c.id = e.category_id
             ORDER BY e.name'
        );
        return array_map([Equipment::class, 'fromRow'], $rows);
    }

    /** @return Equipment[] */
    public function search(?string $query, ?int $categoryId): array
    {
        $sql = 'SELECT e.*, c.name AS category_name
                FROM equipment e
                JOIN categories c ON c.id = e.category_id
                WHERE 1=1';
        $params = [];
        if ($query !== null && $query !== '') {
            $sql .= ' AND (e.name ILIKE :q OR e.description ILIKE :q)';
            $params['q'] = '%' . $query . '%';
        }
        if ($categoryId !== null && $categoryId > 0) {
            $sql .= ' AND e.category_id = :cid';
            $params['cid'] = $categoryId;
        }
        $sql .= ' ORDER BY e.name';
        $rows = $this->fetchAll($sql, $params);
        return array_map([Equipment::class, 'fromRow'], $rows);
    }

    public function findById(int $id): ?Equipment
    {
        $row = $this->fetchOne(
            'SELECT e.*, c.name AS category_name
             FROM equipment e
             JOIN categories c ON c.id = e.category_id
             WHERE e.id = :id',
            ['id' => $id]
        );
        return $row ? Equipment::fromRow($row) : null;
    }

    public function create(Equipment $eq): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO equipment (category_id, name, description, daily_rate, total_quantity, available_quantity)
             VALUES (:cat, :name, :desc, :rate, :total, :avail) RETURNING id'
        );
        $stmt->execute([
            'cat'   => $eq->categoryId,
            'name'  => $eq->name,
            'desc'  => $eq->description,
            'rate'  => $eq->dailyRate,
            'total' => $eq->totalQuantity,
            'avail' => $eq->availableQuantity,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function update(int $id, Equipment $eq): void
    {
        $this->execute(
            'UPDATE equipment
             SET category_id = :cat, name = :name, description = :desc,
                 daily_rate = :rate, total_quantity = :total, available_quantity = :avail
             WHERE id = :id',
            [
                'id'    => $id,
                'cat'   => $eq->categoryId,
                'name'  => $eq->name,
                'desc'  => $eq->description,
                'rate'  => $eq->dailyRate,
                'total' => $eq->totalQuantity,
                'avail' => $eq->availableQuantity,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM equipment WHERE id = :id', ['id' => $id]);
    }
}
