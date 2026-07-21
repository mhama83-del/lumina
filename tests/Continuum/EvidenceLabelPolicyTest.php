<?php
namespace Tests\Continuum;

use CodeIgniter\Test\CIUnitTestCase;
use Continuum\CorePolicy\Domain\EvidenceLabel;
use Continuum\Evidence\Domain\EvidenceLabelPolicy;

final class EvidenceLabelPolicyTest extends CIUnitTestCase
{
    private EvidenceLabelPolicy $p;
    protected function setUp(): void { parent::setUp(); $this->p = new EvidenceLabelPolicy(); }

    public function testSufficiencyMapping(): void
    {
        $this->assertSame(0, $this->p->sufficiency(EvidenceLabel::NeedsEvidence));
        $this->assertSame(1, $this->p->sufficiency(EvidenceLabel::Stated));
        $this->assertSame(0, $this->p->sufficiency(EvidenceLabel::Inferred, candidateConfirmed: false));
        $this->assertSame(1, $this->p->sufficiency(EvidenceLabel::Inferred, candidateConfirmed: true));
        $this->assertSame(2, $this->p->sufficiency(EvidenceLabel::Supported, sourceLinkedAndApproved: true));
        $this->assertSame(3, $this->p->sufficiency(EvidenceLabel::HumanVerified, humanVerified: true));
    }
    public function testCandidateCannotSelfPromoteToSupportedWithoutSource(): void
    {
        $this->assertFalse($this->p->canTransition(EvidenceLabel::Stated, EvidenceLabel::Supported, hasApprovedSource: false));
        $this->assertTrue($this->p->canTransition(EvidenceLabel::Stated, EvidenceLabel::Supported, hasApprovedSource: true));
    }
    public function testHumanVerifiedRequiresAuthorisedVerifier(): void
    {
        $this->assertFalse($this->p->canTransition(EvidenceLabel::Supported, EvidenceLabel::HumanVerified, hasApprovedSource: true, byAuthorisedVerifier: false));
        $this->assertTrue($this->p->canTransition(EvidenceLabel::Supported, EvidenceLabel::HumanVerified, hasApprovedSource: true, byAuthorisedVerifier: true));
    }
}
