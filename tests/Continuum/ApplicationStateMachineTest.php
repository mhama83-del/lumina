<?php
namespace Tests\Continuum;

use CodeIgniter\Test\CIUnitTestCase;
use Continuum\CorePolicy\Domain\ApplicationState as S;
use Continuum\Applications\Domain\ApplicationStateMachine;

final class ApplicationStateMachineTest extends CIUnitTestCase
{
    private ApplicationStateMachine $sm;
    protected function setUp(): void { parent::setUp(); $this->sm = new ApplicationStateMachine(); }

    public function testValidTransition(): void { $this->assertTrue($this->sm->canTransition(S::Submitted, S::Received)); }
    public function testInvalidTransition(): void { $this->assertFalse($this->sm->canTransition(S::Submitted, S::OfferMade)); }
    public function testTerminalHasNoNext(): void { $this->assertSame([], $this->sm->allowedNext(S::NotSelected)); }
    public function testActiveTransitionRequiresExpectedUpdate(): void
    {
        $this->expectException(\DomainException::class);
        $this->sm->transition(S::Received, S::UnderReview, null);
    }
    public function testUnderReviewOwnerIsEmployer(): void
    {
        $res = $this->sm->transition(S::Received, S::UnderReview, new \DateTimeImmutable('+2 days'));
        $this->assertSame('employer', $res['owner']->value);
        $this->assertNotNull($res['expected_update_at']);
    }
    public function testInvalidTransitionThrows(): void
    {
        $this->expectException(\DomainException::class);
        $this->sm->transition(S::Draft, S::OfferMade, new \DateTimeImmutable('+1 day'));
    }
}
