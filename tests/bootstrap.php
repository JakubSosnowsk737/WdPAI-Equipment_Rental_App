<?php
declare(strict_types=1);

// Preferuj autoloader Composera (laduje PHPUnit + klasy App\ i Tests\).
$composer = __DIR__ . '/../vendor/autoload.php';
if (is_file($composer)) {
    require_once $composer;
} else {
    // Fallback: wlasny autoloader aplikacji (gdy uruchamiamy bez Composera).
    require_once __DIR__ . '/../src/Core/Autoloader.php';
    App\Core\Autoloader::register(__DIR__ . '/../src');
}
