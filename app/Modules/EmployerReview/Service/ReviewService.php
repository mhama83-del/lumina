<?php
namespace Continuum\EmployerReview\Service;

use Continuum\CorePolicy\Domain\AvailabilityState;
use Continuum\CorePolicy\Service\AccessPolicy;
use Continuum\CorePolicy\Service\PolicyContext;
use Continuum\Applications\Domain\ConsentPreview;
use Continuum\Roles\Domain\RerEngine;
use Continuum\Roles\Service\RoleReadinessService;

/**
 * Employer review — consent-scoped. Returns ONLY role-scoped, consented information (14 query
 * contract). Never raw survey text, unshared sources, a global candidate list or a numeric score.
 * Produces a GATE label, never a rank.
 */
final class ReviewService
{
    public function __construct(
        private $db,
        private AccessPolicy $access,
        private RoleReadinessService $readiness,
        private RerEngine $rer = new RerEngine(),
        private ConsentPreview $consent = new ConsentPreview()
    ) {}

    /** The employer review payload for one application. Enforces consent + tenant server-side. */
    public function employerView(PolicyContext $ctx, int $applicationId, \DateTimeInterface $now): array
    {
        $app = $this->db->table('applications')->where('id', $applicationId)->get()->getRowArray();
        if (! $app) {
            throw new \RuntimeException('ACCESS_DENIED');
        }

        $consentValid = $this->consentValid($app, $now);
        // Server-side gate: employer tenant + active consent (throws ACCESS_DENIED / CONSENT_REQUIRED).
        $this->access->requireEmployerApplicationConsent($ctx, (int) $app['employer_tenant_id'], $consentValid);

        $avail = $this->availabilityState((int) $app['candidate_id'], $now);
        $r = $this->readiness->readiness((int) $app['candidate_id'], (int) $app['role_version_id']);
        $queue = $this->rer->queueLabel($r['requirements'], $consentValid, $avail->isActive());

        // Evidence summary rebuilt from the SAME allowed set that produced preview_hash.
        $summary = $this->consentedSummary($app);

        return [
            'application_id'     => (int) $applicationId,
            'role_version'       => (int) $app['role_version_id'],
            'queue_label'        => $queue->value,
            'queue_label_text'   => $queue->label(),
            'consent_valid_until'=> $app['consent_snapshot_id'] ? $this->consentExpiry($app) : null,
            'availability'       => $avail->value,
            'requirements'       => $r['breakdown'],       // explainable per requirement
            'questions_to_confirm'=> $r['questions'],
            'evidence_summary'   => $summary,               // == candidate preview
            // NOTE: intentionally NO 'score' / 'rank' field.
        ];
    }

    private function consentValid(array $app, \DateTimeInterface $now): bool
    {
        if (empty($app['consent_snapshot_id'])) {
            return false;
        }
        $c = $this->db->table('consent_snapshots')->where('id', $app['consent_snapshot_id'])->get()->getRowArray();
        if (! $c || ! empty($c['revoked_at'])) {
            return false;
        }
        return empty($c['expires_at']) || new \DateTimeImmutable($c['expires_at']) > $now;
    }

    private function consentExpiry(array $app): ?string
    {
        $c = $this->db->table('consent_snapshots')->where('id', $app['consent_snapshot_id'])->get()->getRowArray();
        return $c['expires_at'] ?? null;
    }

    private function availabilityState(int $candidateId, \DateTimeInterface $now): AvailabilityState
    {
        $p = $this->db->table('availability_pulses')->where('candidate_id', $candidateId)
            ->orderBy('checked_at', 'DESC')->get(1)->getRowArray();
        if (! $p) {
            return AvailabilityState::UnknownStale;
        }
        if (! empty($p['valid_until']) && new \DateTimeImmutable($p['valid_until']) < $now) {
            return AvailabilityState::UnknownStale;
        }
        return AvailabilityState::from($p['state']);
    }

    /** Rebuild the shared summary from allowed_claim_ids — identical to the candidate preview. */
    private function consentedSummary(array $app): array
    {
        if (empty($app['consent_snapshot_id'])) {
            return [];
        }
        $c = $this->db->table('consent_snapshots')->where('id', $app['consent_snapshot_id'])->get()->getRowArray();
        $allowed = json_decode($c['allowed_claim_ids'], true) ?: [];
        if (! $allowed) {
            return [];
        }
        $claims = $this->db->table('evidence_claims')->whereIn('id', $allowed)->get()->getResultArray();
        $all = array_map(function ($cl) {
            $src = $this->db->table('evidence_links el')
                ->select('es.excerpt')
                ->join('evidence_sources es', 'es.id = el.source_id')
                ->where('el.claim_id', $cl['id'])->get()->getRowArray();
            return [
                'id'             => (int) $cl['id'],
                'signal'         => $cl['signal'],
                'label'          => $cl['label'],
                'claim_text'     => $cl['claim_text'],
                'source_excerpt' => $src['excerpt'] ?? null,
                'requirement'    => null,
            ];
        }, $claims);
        return $this->consent->summary($all, $allowed);
    }
}
