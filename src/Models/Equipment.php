<?php
declare(strict_types=1);

namespace App\Models;

final class Equipment
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $categoryId,
        public readonly string $name,
        public readonly ?string $description,
        public readonly float $dailyRate,
        public readonly int $totalQuantity,
        public readonly int $availableQuantity,
        public readonly ?string $categoryName = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:                isset($row['id']) ? (int) $row['id'] : null,
            categoryId:        (int) $row['category_id'],
            name:              (string) $row['name'],
            description:       $row['description'] ?? null,
            dailyRate:         (float) $row['daily_rate'],
            totalQuantity:     (int) $row['total_quantity'],
            availableQuantity: (int) $row['available_quantity'],
            categoryName:      $row['category_name'] ?? null,
        );
    }

    public function isAvailable(): bool
    {
        return $this->availableQuantity > 0;
    }
}
