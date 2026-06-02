<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Csrf;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class CsrfTest extends TestCase
{
    public function testTokenIsStableWithinSession(): void
    {
        $first  = Csrf::token();
        $second = Csrf::token();
        $this->assertSame($first, $second);
        $this->assertNotEmpty($first);
    }

    public function testVerifyAcceptsValidTokenAndRejectsInvalid(): void
    {
        $token = Csrf::token();
        $this->assertTrue(Csrf::verify($token));
        $this->assertFalse(Csrf::verify('zly-token'));
        $this->assertFalse(Csrf::verify(null));
        $this->assertFalse(Csrf::verify(''));
    }
}
