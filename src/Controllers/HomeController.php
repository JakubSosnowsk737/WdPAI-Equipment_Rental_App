<?php
declare(strict_types=1);

namespace App\Controllers;

final class HomeController extends AbstractController
{
    public function index(array $params = []): void
    {
        $this->render('home/index', ['title' => 'WypozyczalniaPRO']);
    }
}
