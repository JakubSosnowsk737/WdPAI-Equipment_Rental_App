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
}
