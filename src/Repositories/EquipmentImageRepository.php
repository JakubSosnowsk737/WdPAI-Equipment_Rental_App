<?php
declare(strict_types=1);

namespace App\Repositories;

final class EquipmentImageRepository extends AbstractRepository
{
    public function add(int $equipmentId, string $path): void
    {
        $this->execute(
            'INSERT INTO equipment_images (equipment_id, image_path) VALUES (:id, :p)',
            ['id' => $equipmentId, 'p' => $path]
        );
    }

    /** @return string[] */
    public function listForEquipment(int $equipmentId): array
    {
        $rows = $this->fetchAll(
            'SELECT image_path FROM equipment_images WHERE equipment_id = :id ORDER BY id',
            ['id' => $equipmentId]
        );
        return array_map(static fn(array $r) => (string) $r['image_path'], $rows);
    }

    /**
     * Pelne wiersze (id + sciezka) - na potrzeby zarzadzania w panelu admina.
     * @return array<int, array{id:int, image_path:string}>
     */
    public function allForEquipment(int $equipmentId): array
    {
        $rows = $this->fetchAll(
            'SELECT id, image_path FROM equipment_images WHERE equipment_id = :id ORDER BY id',
            ['id' => $equipmentId]
        );
        return array_map(
            static fn(array $r) => ['id' => (int) $r['id'], 'image_path' => (string) $r['image_path']],
            $rows
        );
    }

    /** @return array{id:int, equipment_id:int, image_path:string}|null */
    public function findById(int $id): ?array
    {
        $row = $this->fetchOne(
            'SELECT id, equipment_id, image_path FROM equipment_images WHERE id = :id',
            ['id' => $id]
        );
        if ($row === null) {
            return null;
        }
        return [
            'id'           => (int) $row['id'],
            'equipment_id' => (int) $row['equipment_id'],
            'image_path'   => (string) $row['image_path'],
        ];
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM equipment_images WHERE id = :id', ['id' => $id]);
    }
}
