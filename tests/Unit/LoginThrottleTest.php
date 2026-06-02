<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\LoginAttemptRepository;
use App\Services\LoginThrottle;
use PHPUnit\Framework\TestCase;

final class LoginThrottleTest extends TestCase
{
    public function testNotLockedBelowThreshold(): void
    {
        $repo = $this->createMock(LoginAttemptRepository::class);
        $repo->method('countRecentFailures')->willReturn(4);

        $throttle = new LoginThrottle($repo);
        $this->assertFalse($throttle->isLocked('a@b.pl', '127.0.0.1'));
    }

    public function testLockedAtThreshold(): void
    {
        $repo = $this->createMock(LoginAttemptRepository::class);
        $repo->method('countRecentFailures')->willReturn(5);

        $throttle = new LoginThrottle($repo);
        $this->assertTrue($throttle->isLocked('a@b.pl', '127.0.0.1'));
    }

    public function testSuccessClearsFailures(): void
    {
        $repo = $this->createMock(LoginAttemptRepository::class);
        $repo->expects($this->once())->method('record')->with('a@b.pl', '127.0.0.1', true);
        $repo->expects($this->once())->method('clearFailures')->with('a@b.pl');

        $throttle = new LoginThrottle($repo);
        $throttle->registerSuccess('a@b.pl', '127.0.0.1');
    }
}
