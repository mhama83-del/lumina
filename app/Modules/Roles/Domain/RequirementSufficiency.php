<?php
namespace Continuum\Roles\Domain;

use Continuum\CorePolicy\Domain\Importance;

/**
 * Value object: one role requirement's importance + the candidate's effective evidence
 * sufficiency (0..3) for it. Sufficiency MUST come from EvidenceLabelPolicy — never from a
 * taxonomy graph edge (05 §3, D-003). Immutable.
 */
final class RequirementSufficiency
{
    public function __construct(
        public readonly string $requirementLabel,
        public readonly Importance $importance,
        public readonly int $sufficiency,           // 0..3, already capped by EvidenceLabelPolicy
        public readonly string $explanation = ''
    ) {
        if ($sufficiency < 0 || $sufficiency > 3) {
            throw new \InvalidArgumentException('Sufficiency must be 0..3');
        }
    }

    public function weight(): int
    {
        return $this->importance->weight();
    }
}
