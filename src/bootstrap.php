<?php
declare(strict_types=1);

require_once __DIR__ . '/Core/Autoloader.php';

App\Core\Autoloader::register(__DIR__);
App\Core\Config::load(dirname(__DIR__) . '/.env');
