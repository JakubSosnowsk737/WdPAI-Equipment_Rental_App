<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Models\Rental;

final class RentalRepository extends AbstractRepository
{
    public function create(Rental $rental): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO rentals (user_id, status, start_date, end_date, total_cost)
             VALUES (:uid, :st, :sd, :ed, :cost) RETURNING id'
        );
        $stmt->execute([
            'uid'  => $rental->userId,
            'st'   => $rental->status,
            'sd'   => $rental->startDate,
            'ed'   => $rental->endDate,
            'cost' => $rental->totalCost,
        ]);
        return (int) $stmt->fetchColumn();
    }

    public function addItem(int $rentalId, int $equipmentId, int $qty, float $dailyRate): void
    {
        $this->execute(
            'INSERT INTO rental_items (rental_id, equipment_id, quantity, daily_rate)
             VALUES (:r, :e, :q, :d)',
            ['r' => $rentalId, 'e' => $equipmentId, 'q' => $qty, 'd' => $dailyRate]
        );
    }

    /** @return Rental[] */
    public function findByUser(int $userId): array
    {
        $rows = $this->fetchAll(
            'SELECT * FROM rentals WHERE user_id = :uid ORDER BY created_at DESC',
            ['uid' => $userId]
        );
        return array_map([Rental::class, 'fromRow'], $rows);
    }

    /** @return Rental[] */
    public function findAllWithUser(): array
    {
        $rows = $this->fetchAll(
            "SELECT r.*, u.first_name || ' ' || u.last_name AS customer_name
             FROM rentals r
             JOIN users u ON u.id = r.user_id
             ORDER BY r.created_at DESC"
        );
        return array_map([Rental::class, 'fromRow'], $rows);
    }

    public function findById(int $id): ?Rental
    {
        $row = $this->fetchOne('SELECT * FROM rentals WHERE id = :id', ['id' => $id]);
        return $row ? Rental::fromRow($row) : null;
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->execute(
            'UPDATE rentals SET status = :st WHERE id = :id',
            ['st' => $status, 'id' => $id]
        );
    }
}
