<?php
namespace Continuum\Applications\Service;

use Continuum\CorePolicy\Domain\ApplicationState as S;
use Continuum\CorePolicy\Service\AuditService;
use Continuum\CorePolicy\Service\PolicyContext;
use Continuum\Applications\Domain\ApplicationStateMachine;
use Continuum\Applications\Domain\StaleDetection;

/**
 * Application timeline + state transitions + stale sweep (06). Persists domain events; sessions are
 * never the source of truth. Every active transition writes owner + expected_update + audit event.
 */
final class ApplicationService
{
    public function __construct(
        private $db,
        private AuditService $audit,
        private ApplicationStateMachine $sm = new ApplicationStateMachine(),
        private ?StaleDetection $stale = null
    ) {
        $this->stale ??= new StaleDetection(24);
    }

    /** Candidate-facing Outcome Loop view — the answer to ghosting. */
    public function candidateView(int $applicationId): array
    {
        $app = $this->db->table('applications')->where('id', $applicationId)->get()->getRowArray();
        $events = $this->db->table('application_events')
            ->where('application_id', $applicationId)
            ->orderBy('occurred_at', 'ASC')->get()->getResultArray();

        $state = S::from($app['state']);
        return [
            'state'                => $state->value,
            'state_label'          => $state->label(),
            'last_verified_action' => $app['last_verified_action'],
            'next_owner'           => $app['current_owner'],
            'expected_update_at'   => $app['expected_update_at'],
            'is_terminal'          => $state->isTerminal(),
            'timeline'             => array_map(fn ($e) => [
                'type'        => $e['type'],
                'occurred_at' => $e['occurred_at'],
                'note'        => json_decode($e['payload_json'] ?? '{}', true)['note'] ?? '',
            ], $events),
        ];
    }

    /**
     * Change application state via the state machine, persisting owner + expected update + event.
     * @throws \DomainException on invalid transition or missing expected_update_at.
     */
    public function changeState(
        PolicyContext $ctx,
        int $applicationId,
        S $to,
        ?\DateTimeInterface $expectedUpdateAt,
        string $note
    ): void {
        $app = $this->db->table('applications')->where('id', $applicationId)->get()->getRowArray();
        $from = S::from($app['state']);
        $result = $this->sm->transition($from, $to, $expectedUpdateAt);

        $this->db->transStart();
        $this->db->table('applications')->where('id', $applicationId)->update([
            'state'                => $result['state']->value,
            'current_owner'        => $result['owner']?->value,
            'expected_update_at'   => $result['expected_update_at']?->format('Y-m-d H:i:s'),
            'last_verified_action' => $note,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('application_events')->insert([
            'application_id' => $applicationId,
            'type'           => $to->value,
            'actor'          => $ctx->identityKey,
            'visible_to'     => 'candidate,employer',
            'payload_json'   => json_encode(['note' => $note]),
            'occurred_at'    => date('Y-m-d H:i:s'),
        ]);
        $this->db->transComplete();

        $this->audit->record($ctx, 'application.status_changed', 'application', (string) $applicationId, 'ok',
            ['from' => $from->value, 'to' => $to->value]);
    }

    /** Stale sweep: emits stale events and returns operator exceptions (no hiring outcome). */
    public function staleSweep(\DateTimeInterface $now): array
    {
        $active = $this->db->table('applications')
            ->whereNotIn('state', ['draft','offer_accepted','offer_declined','not_selected','withdrawn','expired_closed'])
            ->where('expected_update_at <', $now->format('Y-m-d H:i:s'))
            ->get()->getResultArray();

        $exceptions = [];
        foreach ($active as $a) {
            $state = S::from($a['state']);
            $eua = $a['expected_update_at'] ? new \DateTimeImmutable($a['expected_update_at']) : null;
            if ($this->stale->isStale($state, $eua, $now)) {
                $this->db->table('application_events')->insert([
                    'application_id' => $a['id'],
                    'type'           => 'update_became_stale',
                    'actor'          => 'system',
                    'visible_to'     => 'candidate,employer,operator',
                    'payload_json'   => json_encode(['note' => 'Expected update passed']),
                    'occurred_at'    => $now->format('Y-m-d H:i:s'),
                ]);
            }
            if ($this->stale->isOperatorException($state, $eua, $now)) {
                $exceptions[] = [
                    'application_id'     => (int) $a['id'],
                    'employer_tenant_id' => (int) $a['employer_tenant_id'],
                    'expected_update_at' => $a['expected_update_at'],
                    'candidate_message'  => $this->stale->candidateMessage(),
                ];
            }
        }
        return $exceptions;
    }
}
