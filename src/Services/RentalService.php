<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Rental;
use App\Repositories\EquipmentRepository;
use App\Repositories\RentalRepository;
use PDO;
use RuntimeException;

/**
 * Tworzenie wypozyczenia w transakcji SERIALIZABLE -
 * zapobiega podwojnemu wypozyczeniu tego samego egzemplarza.
 */
final class RentalService
{
    public function __construct(
        private RentalRepository $rentals,
        private EquipmentRepository $equipment,
    ) {}

    /**
     * Zwrot - przywraca ilości sprzętu i zmienia status na 'zakonczone'.
     * @return array{ok:bool, error?:string}
     */
    public function returnRental(int $rentalId): array
    {
        $pdo = Database::getInstance()->pdo();
        $pdo->beginTransaction();
        try {
            $rental = $this->rentals->findById($rentalId);
            if ($rental === null) {
                throw new RuntimeException('Wypożyczenie nie istnieje.');
            }
            if ($rental->status === Rental::STATUS_FINISHED) {
                throw new RuntimeException('Wypożyczenie jest już zakończone.');
            }

            $items = $pdo->prepare('SELECT equipment_id, quantity FROM rental_items WHERE rental_id = :id');
            $items->execute(['id' => $rentalId]);
            foreach ($items->fetchAll(PDO::FETCH_ASSOC) as $it) {
                $upd = $pdo->prepare(
                    'UPDATE equipment SET available_quantity = available_quantity + :q
                     WHERE id = :id AND available_quantity + :q <= total_quantity'
                );
                $upd->execute(['q' => (int) $it['quantity'], 'id' => (int) $it['equipment_id']]);
            }

            $this->rentals->updateStatus($rentalId, Rental::STATUS_FINISHED);
            $pdo->commit();
            return ['ok' => true];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Maksymalny okres pojedynczego wypożyczenia (dni). */
    private const MAX_DAYS = 30;

    public function rent(int $userId, int $equipmentId, int $quantity, string $startDate, string $endDate): array
    {
        if ($quantity < 1) {
            return ['ok' => false, 'error' => 'Ilość musi być dodatnia.'];
        }

        // Ścisła walidacja dat po stronie serwera (niezależna od JS/przeglądarki).
        $start = \DateTimeImmutable::createFromFormat('!Y-m-d', $startDate);
        $end   = \DateTimeImmutable::createFromFormat('!Y-m-d', $endDate);
        $today = new \DateTimeImmutable('today');
        if ($start === false || $end === false
            || $start->format('Y-m-d') !== $startDate
            || $end->format('Y-m-d') !== $endDate) {
            return ['ok' => false, 'error' => 'Nieprawidłowy format daty.'];
        }
        if ($start < $today) {
            return ['ok' => false, 'error' => 'Data początku nie może być w przeszłości.'];
        }
        if ($end < $start) {
            return ['ok' => false, 'error' => 'Data końca musi być nie wcześniejsza niż data początku.'];
        }
        $days = $end->diff($start)->days + 1;
        if ($days > self::MAX_DAYS) {
            return ['ok' => false, 'error' => 'Maksymalny okres wypożyczenia to ' . self::MAX_DAYS . ' dni.'];
        }

        $pdo = Database::getInstance()->pdo();
        $pdo->exec('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM equipment WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $equipmentId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new RuntimeException('Sprzęt nie istnieje.');
            }
            if ((int) $row['available_quantity'] < $quantity) {
                throw new RuntimeException('Niewystarczająca ilość dostępnego sprzętu.');
            }
            $rate = (float) $row['daily_rate'];

            $cost = round($rate * $quantity * $days, 2);

            $rental = new Rental(
                id:        null,
                userId:    $userId,
                status:    Rental::STATUS_NEW,
                startDate: $startDate,
                endDate:   $endDate,
                totalCost: $cost,
            );
            $rentalId = $this->rentals->create($rental);
            // Trigger rental_items_after_insert sam zmniejszy available_quantity.
            $this->rentals->addItem($rentalId, $equipmentId, $quantity, $rate);

            $pdo->commit();
            return ['ok' => true, 'rentalId' => $rentalId];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
