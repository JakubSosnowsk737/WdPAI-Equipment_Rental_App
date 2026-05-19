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
     * @return array{ok:bool, rentalId?:int, error?:string}
     */
    /**
     * Zwrot - przywraca ilosci sprzetu i zmienia status na 'zakonczone'.
     * @return array{ok:bool, error?:string}
     */
    public function returnRental(int $rentalId): array
    {
        $pdo = Database::getInstance()->pdo();
        $pdo->beginTransaction();
        try {
            $rental = $this->rentals->findById($rentalId);
            if ($rental === null) {
                throw new RuntimeException('Wypozyczenie nie istnieje.');
            }
            if ($rental->status === Rental::STATUS_FINISHED) {
                throw new RuntimeException('Juz zakonczone.');
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

    public function rent(int $userId, int $equipmentId, int $quantity, string $startDate, string $endDate): array
    {
        if ($startDate > $endDate) {
            return ['ok' => false, 'error' => 'Data od musi byc <= data do.'];
        }
        if ($quantity < 1) {
            return ['ok' => false, 'error' => 'Ilosc musi byc dodatnia.'];
        }

        $pdo = Database::getInstance()->pdo();
        $pdo->exec('SET TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT * FROM equipment WHERE id = :id FOR UPDATE');
            $stmt->execute(['id' => $equipmentId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                throw new RuntimeException('Sprzet nie istnieje.');
            }
            if ((int) $row['available_quantity'] < $quantity) {
                throw new RuntimeException('Niewystarczajaca ilosc.');
            }
            $rate = (float) $row['daily_rate'];

            $days = (new \DateTimeImmutable($endDate))->diff(new \DateTimeImmutable($startDate))->days + 1;
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
            $this->rentals->addItem($rentalId, $equipmentId, $quantity, $rate);

            // Trigger zaktualizuje available_quantity automatycznie (commit #42).
            // Do czasu jego wdrozenia robimy to recznie tutaj.
            $upd = $pdo->prepare('UPDATE equipment SET available_quantity = available_quantity - :q WHERE id = :id');
            $upd->execute(['q' => $quantity, 'id' => $equipmentId]);

            $pdo->commit();
            return ['ok' => true, 'rentalId' => $rentalId];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
