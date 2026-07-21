<?php
namespace Continuum\CorePolicy\Service;

use Continuum\CorePolicy\Domain\RoleType;

/**
 * The authenticated actor context for the current request. In DEMO_MODE the Scenario Switcher
 * swaps which demo identity is active, but this is ONLY a context swap — every sensitive
 * read/write still runs AccessPolicy + consent checks (D-006). It is never authorization itself.
 */
final class PolicyContext
{
    public function __construct(
        public readonly string $identityKey,   // e.g. c01_amina
        public readonly RoleType $role,
        public readonly int $subjectId,        // candidate_id / employer_id / university_id / operator_id
        public readonly ?int $tenantId = null, // employer/university org scope
        public readonly bool $demo = true
    ) {}

    public function isRole(RoleType $r): bool
    {
        return $this->role === $r;
    }
}
