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
