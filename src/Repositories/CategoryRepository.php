<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;

final class CategoryRepository extends AbstractRepository
{
    /** @return Category[] */
    public function findAll(): array
    {
        $rows = $this->fetchAll('SELECT * FROM categories ORDER BY name');
        return array_map([Category::class, 'fromRow'], $rows);
    }

    public function findById(int $id): ?Category
    {
        $row = $this->fetchOne('SELECT * FROM categories WHERE id = :id', ['id' => $id]);
        return $row ? Category::fromRow($row) : null;
    }
}
