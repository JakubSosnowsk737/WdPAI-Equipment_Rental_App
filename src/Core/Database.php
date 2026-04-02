<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Singleton dostepu do bazy PostgreSQL przez PDO.
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $host = Config::get('DB_HOST', 'db');
        $port = Config::get('DB_PORT', '5432');
        $name = Config::get('DB_NAME', 'wypozyczalnia');
        $user = Config::get('DB_USER', 'app');
        $pass = Config::get('DB_PASSWORD', 'app_secret');

        $dsn = "pgsql:host=$host;port=$port;dbname=$name";

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('Brak polaczenia z baza: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function getInstance(): Database
    {
        return self::$instance ??= new self();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
