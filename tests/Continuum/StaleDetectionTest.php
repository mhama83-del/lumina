<?php
namespace Tests\Continuum;

use CodeIgniter\Test\CIUnitTestCase;
use Continuum\CorePolicy\Domain\ApplicationState as S;
use Continuum\Applications\Domain\StaleDetection;

final class StaleDetectionTest extends CIUnitTestCase
{
    public function testOverdueActiveIsStale(): void
    {
        $sd = new StaleDetection(24);
        $now = new \DateTimeImmutable('2026-07-21 12:00:00');
        $this->assertTrue($sd->isStale(S::UnderReview, new \DateTimeImmutable('2026-07-20 12:00:00'), $now));
        $this->assertFalse($sd->isStale(S::UnderReview, new \DateTimeImmutable('2026-07-22 12:00:00'), $now));
        $this->assertFalse($sd->isStale(S::NotSelected, new \DateTimeImmutable('2026-07-20 12:00:00'), $now));
    }
    public function testOperatorExceptionAfterGrace(): void
    {
        $sd = new StaleDetection(24);
        $now = new \DateTimeImmutable('2026-07-21 12:00:00');
        $this->assertFalse($sd->isOperatorException(S::UnderReview, new \DateTimeImmutable('2026-07-20 12:00:00'), $now));
        $this->assertTrue($sd->isOperatorException(S::UnderReview, new \DateTimeImmutable('2026-07-19 00:00:00'), $now));
    }
}
