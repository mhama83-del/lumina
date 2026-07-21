<?php
namespace Tests\Continuum;

use CodeIgniter\Test\CIUnitTestCase;
use Continuum\CorePolicy\Domain\Importance;
use Continuum\CorePolicy\Domain\QueueLabel;
use Continuum\Roles\Domain\RerEngine;
use Continuum\Roles\Domain\RequirementSufficiency as Req;

/** Mandatory unit tests: RER formula, gates, max sufficiency, zero, graph-edge no credit (09). */
final class RerEngineTest extends CIUnitTestCase
{
    private RerEngine $rer;
    protected function setUp(): void { parent::setUp(); $this->rer = new RerEngine(); }

    public function testFullyVerifiedCriticalsGive100(): void
    {
        $r = [new Req('SQL', Importance::Critical, 3), new Req('Py', Importance::Critical, 3)];
        $this->assertEqualsWithDelta(100.0, $this->rer->rer($r), 0.05);
    }
    public function testNoRequirementsGiveZero(): void
    {
        $this->assertEqualsWithDelta(0.0, $this->rer->rer([]), 0.05);
    }
    public function testMixedWeights(): void
    {
        $r = [new Req('SQL', Importance::Critical, 2), new Req('C', Importance::Important, 1), new Req('D', Importance::Supporting, 0)];
        $this->assertEqualsWithDelta(44.4, $this->rer->rer($r), 0.05);
    }
    public function testSufficiencyAboveThreeRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Req('x', Importance::Critical, 4);
    }
    public function testGraphEdgeOnlyRequirementContributesZero(): void
    {
        // A related-skill graph edge yields sufficiency 0 (never partial credit).
        $r = [new Req('SQL', Importance::Critical, 0, 'related edge only')];
        $this->assertEqualsWithDelta(0.0, $this->rer->rer($r), 0.05);
    }
    public function testReviewNowGate(): void
    {
        $r = [new Req('SQL', Importance::Critical, 2)];
        $this->assertSame(QueueLabel::ReviewNow, $this->rer->queueLabel($r, true, true));
    }
    public function testReviewWithQuestionsGate(): void
    {
        $r = [new Req('SQL', Importance::Critical, 1), new Req('Py', Importance::Critical, 2)];
        $this->assertSame(QueueLabel::ReviewWithQuestions, $this->rer->queueLabel($r, true, true));
    }
    public function testCandidateActionWhenCriticalZero(): void
    {
        $r = [new Req('SQL', Importance::Critical, 0)];
        $this->assertSame(QueueLabel::CandidateActionSuggested, $this->rer->queueLabel($r, true, true));
    }
    public function testConsentAndAvailabilityAreGatesNotPoints(): void
    {
        $r = [new Req('SQL', Importance::Critical, 3)];
        $this->assertSame(QueueLabel::CandidateActionSuggested, $this->rer->queueLabel($r, false, true));
        $this->assertSame(QueueLabel::CandidateActionSuggested, $this->rer->queueLabel($r, true, false));
    }
    public function testPotentialCoverageChange(): void
    {
        $all = [new Req('SQL', Importance::Critical, 1)];
        $this->assertEqualsWithDelta(33.3, $this->rer->potentialCoverageChange($all[0], 2, $all), 0.05);
    }
}
