<?php
declare(strict_types=1);

namespace App\Models;

final class Category
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $description = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:          isset($row['id']) ? (int) $row['id'] : null,
            name:        (string) $row['name'],
            description: $row['description'] ?? null,
        );
    }
}
