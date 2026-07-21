<?php
namespace Continuum\Applications\Domain;

use Continuum\CorePolicy\Domain\ApplicationState;

/**
 * Stale detection (06_OUTCOME_LOOP.md §Stale detection). Framework-independent, pure decisions.
 *
 * An active application whose expected_update_at has passed is STALE and emits
 * application.update_became_stale. After a configurable grace period it becomes a Talentbank
 * operator EXCEPTION. The operator may remind/escalate but never decides who is hired.
 */
final class StaleDetection
{
    public function __construct(private int $graceHours = 24) {}

    public function isStale(
        ApplicationState $state,
        ?\DateTimeInterface $expectedUpdateAt,
        \DateTimeInterface $now
    ): bool {
        if (! $state->requiresOwnerAndExpectedUpdate() || $expectedUpdateAt === null) {
            return false;
        }
        return $expectedUpdateAt < $now;
    }

    /** True once the grace window after the expected update has also elapsed. */
    public function isOperatorException(
        ApplicationState $state,
        ?\DateTimeInterface $expectedUpdateAt,
        \DateTimeInterface $now
    ): bool {
        if (! $this->isStale($state, $expectedUpdateAt, $now)) {
            return false;
        }
        $deadline = (clone $expectedUpdateAt instanceof \DateTimeImmutable
            ? $expectedUpdateAt
            : \DateTimeImmutable::createFromInterface($expectedUpdateAt)
        )->modify("+{$this->graceHours} hours");
        return $deadline < $now;
    }

    /** Truthful, neutral candidate-facing message — never a fabricated employer status. */
    public function candidateMessage(): string
    {
        return 'This application is awaiting an update. We have flagged it for follow-up.';
    }
}
