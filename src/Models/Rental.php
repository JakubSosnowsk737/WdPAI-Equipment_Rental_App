<?php
declare(strict_types=1);

namespace App\Models;

final class Rental
{
    public const STATUS_NEW       = 'nowe';
    public const STATUS_ACTIVE    = 'aktywne';
    public const STATUS_FINISHED  = 'zakonczone';
    public const STATUS_CANCELLED = 'anulowane';

    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $status,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly float $totalCost,
        public readonly ?string $createdAt = null,
        public readonly ?string $customerName = null,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:           isset($row['id']) ? (int) $row['id'] : null,
            userId:       (int) $row['user_id'],
            status:       (string) $row['status'],
            startDate:    (string) $row['start_date'],
            endDate:      (string) $row['end_date'],
            totalCost:    (float) $row['total_cost'],
            createdAt:    $row['created_at'] ?? null,
            customerName: $row['customer_name'] ?? null,
        );
    }

    public function days(): int
    {
        $start = new \DateTimeImmutable($this->startDate);
        $end   = new \DateTimeImmutable($this->endDate);
        return (int) $end->diff($start)->days + 1;
    }

    /** Czytelna etykieta statusu po polsku. */
    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_NEW       => 'Nowe',
            self::STATUS_ACTIVE    => 'Aktywne',
            self::STATUS_FINISHED  => 'Zakończone',
            self::STATUS_CANCELLED => 'Anulowane',
            default                => $this->status,
        };
    }
}
