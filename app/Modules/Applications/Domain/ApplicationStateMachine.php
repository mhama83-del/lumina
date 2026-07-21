<?php
namespace Continuum\Applications\Domain;

use Continuum\CorePolicy\Domain\ApplicationState as S;
use Continuum\CorePolicy\Domain\RoleType;

/**
 * Application state machine (06_OUTCOME_LOOP.md). Framework-independent.
 *
 * Enforces:
 *  - only declared transitions are valid (APPLICATION_TRANSITION_INVALID otherwise);
 *  - every transition into an active (non-draft, non-terminal) state carries next owner +
 *    expected update — the system answer to ghosting.
 */
final class ApplicationStateMachine
{
    /** @var array<string, string[]> allowed target states keyed by source state value */
    private const TRANSITIONS = [
        'draft'                   => ['submitted', 'withdrawn'],
        'submitted'               => ['received', 'withdrawn', 'expired_closed'],
        'received'                => ['under_review', 'withdrawn', 'expired_closed'],
        'under_review'            => ['clarification_requested', 'interview_invited', 'decision_pending', 'not_selected', 'withdrawn', 'expired_closed'],
        'clarification_requested' => ['under_review', 'withdrawn', 'expired_closed'],
        'interview_invited'       => ['interview_scheduled', 'withdrawn', 'expired_closed'],
        'interview_scheduled'     => ['interview_completed', 'withdrawn', 'expired_closed'],
        'interview_completed'     => ['decision_pending', 'not_selected', 'withdrawn', 'expired_closed'],
        'decision_pending'        => ['offer_made', 'not_selected', 'withdrawn', 'expired_closed'],
        'offer_made'              => ['offer_accepted', 'offer_declined', 'expired_closed'],
        // terminal states have no outbound transitions
    ];

    /** Default next owner for a given state (06 state contract). */
    private const DEFAULT_OWNER = [
        'submitted'                => RoleType::Operator,   // System/Talentbank ack
        'received'                 => RoleType::Employer,
        'under_review'             => RoleType::Employer,
        'clarification_requested'  => RoleType::Candidate,
        'interview_invited'        => RoleType::Candidate,
        'interview_scheduled'      => RoleType::Employer,
        'interview_completed'      => RoleType::Employer,
        'decision_pending'         => RoleType::Employer,
        'offer_made'               => RoleType::Candidate,
    ];

    public function canTransition(S $from, S $to): bool
    {
        return in_array($to->value, self::TRANSITIONS[$from->value] ?? [], true);
    }

    /** @return S[] */
    public function allowedNext(S $from): array
    {
        return array_map(static fn (string $v) => S::from($v), self::TRANSITIONS[$from->value] ?? []);
    }

    public function defaultOwner(S $state): ?RoleType
    {
        $owner = self::DEFAULT_OWNER[$state->value] ?? null;
        return $owner;
    }

    /**
     * Validate and describe a transition. Throws on invalid transition or on a missing
     * expected-update timestamp when the target state remains active.
     *
     * @param \DateTimeInterface|null $expectedUpdateAt required when target is active
     * @return array{state:S, owner:?RoleType, expected_update_at:?\DateTimeInterface}
     */
    public function transition(S $from, S $to, ?\DateTimeInterface $expectedUpdateAt): array
    {
        if (! $this->canTransition($from, $to)) {
            throw new \DomainException("APPLICATION_TRANSITION_INVALID: {$from->value} -> {$to->value}");
        }
        if ($to->requiresOwnerAndExpectedUpdate() && $expectedUpdateAt === null) {
            throw new \DomainException("Active state {$to->value} requires expected_update_at");
        }
        return [
            'state'              => $to,
            'owner'              => $this->defaultOwner($to),
            'expected_update_at' => $to->requiresOwnerAndExpectedUpdate() ? $expectedUpdateAt : null,
        ];
    }
}
