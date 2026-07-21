<?php
namespace Continuum\CorePolicy\Service;

use Continuum\CorePolicy\Domain\RoleType;

/**
 * Server-side access policy (04_DATA_MODEL_AND_CONSENT.md §Access model).
 * Every sensitive read/write must pass:
 *   authenticated actor AND tenant scope AND role permission AND resource ownership/relationship
 *   AND active consent (when candidate evidence is involved) AND audit event.
 * Controllers/UI hiding alone is NOT sufficient — call these methods in services.
 *
 * Throws AccessDeniedException on failure (maps to ACCESS_DENIED, never leaking existence/consent).
 */
final class AccessPolicy
{
    public function requireRole(PolicyContext $ctx, RoleType ...$allowed): void
    {
        if (! in_array($ctx->role, $allowed, true)) {
            throw new AccessDeniedException('ACCESS_DENIED');
        }
    }

    /** Candidate may act only on their own subject. */
    public function requireOwnCandidate(PolicyContext $ctx, int $candidateId): void
    {
        $this->requireRole($ctx, RoleType::Candidate);
        if ($ctx->subjectId !== $candidateId) {
            throw new AccessDeniedException('ACCESS_DENIED');
        }
    }

    /** Employer may act only within their own tenant/role. */
    public function requireEmployerTenant(PolicyContext $ctx, int $employerTenantId): void
    {
        $this->requireRole($ctx, RoleType::Employer);
        if ($ctx->tenantId !== $employerTenantId) {
            throw new AccessDeniedException('ACCESS_DENIED');
        }
    }

    /**
     * Employer may see candidate evidence ONLY when: it is an application to their own role AND a
     * still-valid consent snapshot exists for that application (04 §Employer visibility).
     */
    public function requireEmployerApplicationConsent(
        PolicyContext $ctx,
        int $applicationEmployerTenantId,
        bool $consentValidNow
    ): void {
        $this->requireEmployerTenant($ctx, $applicationEmployerTenantId);
        if (! $consentValidNow) {
            throw new AccessDeniedException('CONSENT_REQUIRED');
        }
    }
}
