<?php
namespace Tests\Continuum;

use CodeIgniter\Test\CIUnitTestCase;
use Continuum\CorePolicy\Domain\RoleType;
use Continuum\CorePolicy\Service\AccessPolicy;
use Continuum\CorePolicy\Service\PolicyContext;
use Continuum\CorePolicy\Service\AccessDeniedException;

final class AccessPolicyTest extends CIUnitTestCase
{
    private AccessPolicy $p;
    protected function setUp(): void { parent::setUp(); $this->p = new AccessPolicy(); }

    private function ctx(RoleType $r, int $subject, ?int $tenant): PolicyContext
    { return new PolicyContext('demo', $r, $subject, $tenant, true); }

    public function testWrongRoleDenied(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->p->requireRole($this->ctx(RoleType::Candidate, 1, null), RoleType::Employer);
    }
    public function testCandidateCannotTouchOthersData(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->p->requireOwnCandidate($this->ctx(RoleType::Candidate, 1, null), 2);
    }
    public function testCandidateOwnDataOk(): void
    {
        $this->p->requireOwnCandidate($this->ctx(RoleType::Candidate, 1, null), 1);
        $this->assertTrue(true);
    }
    public function testEmployerCannotCrossTenant(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->p->requireEmployerTenant($this->ctx(RoleType::Employer, 1, 1), 2);
    }
    public function testEmployerOwnTenantOk(): void
    {
        $this->p->requireEmployerTenant($this->ctx(RoleType::Employer, 1, 1), 1);
        $this->assertTrue(true);
    }
    public function testEmployerWithoutConsentDenied(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->p->requireEmployerApplicationConsent($this->ctx(RoleType::Employer, 1, 1), 1, false);
    }
}
