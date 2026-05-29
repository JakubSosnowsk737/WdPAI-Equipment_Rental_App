<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

final class UserModelTest extends TestCase
{
    public function testFullNameConcatenates(): void
    {
        $u = new User(1, 'a@b.pl', 'h', 'Jan', 'Kowalski', User::ROLE_CLIENT);
        $this->assertSame('Jan Kowalski', $u->fullName());
    }

    public function testIsAdminTrueOnlyForAdmin(): void
    {
        $admin  = new User(1, 'a@b.pl', 'h', 'Jan', 'K.', User::ROLE_ADMIN);
        $client = new User(2, 'c@b.pl', 'h', 'Anna', 'K.', User::ROLE_CLIENT);
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($client->isAdmin());
    }

    public function testFromRowMapsColumns(): void
    {
        $u = User::fromRow([
            'id'            => 7,
            'email'         => 'x@y.pl',
            'password_hash' => 'h',
            'first_name'    => 'Anna',
            'last_name'     => 'Nowak',
            'role'          => 'pracownik',
        ]);
        $this->assertSame(7, $u->id);
        $this->assertTrue($u->isEmployee());
    }
}
