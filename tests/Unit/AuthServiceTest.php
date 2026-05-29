<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use PHPUnit\Framework\TestCase;

final class AuthServiceTest extends TestCase
{
    public function testRegisterReturnsValidationErrorsForBadInput(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $svc  = new AuthService($repo);

        $errors = $svc->register([
            'email'      => 'not-an-email',
            'password'   => '123',
            'first_name' => '',
            'last_name'  => '',
        ]);
        $this->assertNotEmpty($errors);
    }

    public function testVerifyReturnsNullOnUnknownEmail(): void
    {
        $repo = $this->createMock(UserRepository::class);
        $repo->method('findByEmail')->willReturn(null);
        $svc  = new AuthService($repo);

        $this->assertNull($svc->verify('a@b.pl', 'haslo123'));
    }

    public function testVerifyReturnsUserOnMatchingPassword(): void
    {
        $hash = password_hash('tajne', PASSWORD_BCRYPT);
        $user = new User(1, 'a@b.pl', $hash, 'Jan', 'K.', User::ROLE_CLIENT);

        $repo = $this->createMock(UserRepository::class);
        $repo->method('findByEmail')->willReturn($user);

        $svc  = new AuthService($repo);
        $this->assertSame($user, $svc->verify('a@b.pl', 'tajne'));
        $this->assertNull($svc->verify('a@b.pl', 'zle'));
    }
}
