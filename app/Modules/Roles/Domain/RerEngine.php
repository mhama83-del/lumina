<?php
namespace Continuum\Roles\Domain;

use Continuum\CorePolicy\Domain\Importance;
use Continuum\CorePolicy\Domain\QueueLabel;

/**
 * Role Evidence Readiness engine — the ONE place the RER formula lives (05 §3, §4, §6).
 *
 * RER(role) = 100 × Σ(weight × sufficiency) ÷ [3 × Σ(weight)]
 *
 * RER is role-specific evidence coverage. It is NOT a hiring probability, candidate rank or
 * employability score. Consent and availability are queue GATES, never RER points.
 *
 * Framework-independent so it is unit-testable without CI4 or a database.
 */
final class RerEngine
{
    /**
     * @param RequirementSufficiency[] $requirements
     * @return float 0..100, rounded to 1 dp. 0 requirements => 0.0.
     */
    public function rer(array $requirements): float
    {
        $weightSum = 0;
        $weighted  = 0;
        foreach ($requirements as $r) {
            $weightSum += $r->weight();
            $weighted  += $r->weight() * $r->sufficiency;
        }
        if ($weightSum === 0) {
            return 0.0;
        }
        return round(100 * $weighted / (3 * $weightSum), 1);
    }

    /**
     * Per-requirement contribution breakdown for the explainer UI ("explainable per requirement").
     * @param RequirementSufficiency[] $requirements
     * @return array<int,array{requirement:string,importance:string,weight:int,sufficiency:int,max:int,explanation:string}>
     */
    public function breakdown(array $requirements): array
    {
        $out = [];
        foreach ($requirements as $r) {
            $out[] = [
                'requirement' => $r->requirementLabel,
                'importance'  => $r->importance->value,
                'weight'      => $r->weight(),
                'sufficiency' => $r->sufficiency,
                'max'         => 3,
                'explanation' => $r->explanation,
            ];
        }
        return $out;
    }

    /**
     * Employer review queue GATE label (05 §4). Uses Critical requirements + consent/availability
     * gates only. Never produces a rank or ordering.
     *
     * @param RequirementSufficiency[] $requirements
     */
    public function queueLabel(
        array $requirements,
        bool $consentValid,
        bool $availabilityActive
    ): QueueLabel {
        $criticals = array_filter(
            $requirements,
            fn (RequirementSufficiency $r) => $r->importance === Importance::Critical
        );

        $anyCriticalZero = false;
        $anyCriticalBelowTwo = false;
        foreach ($criticals as $c) {
            if ($c->sufficiency === 0)  { $anyCriticalZero = true; }
            if ($c->sufficiency < 2)    { $anyCriticalBelowTwo = true; }
        }

        // Gates first: absent/expired consent or stale/unknown availability => candidate action.
        if (! $consentValid || ! $availabilityActive || $anyCriticalZero) {
            return QueueLabel::CandidateActionSuggested;
        }
        // Every Critical >= 1 here. If any Critical < 2 => review with questions.
        if ($anyCriticalBelowTwo) {
            return QueueLabel::ReviewWithQuestions;
        }
        // Every Critical >= 2, consent valid, availability active.
        return QueueLabel::ReviewNow;
    }

    /**
     * Potential coverage change if a specific requirement is raised to a target sufficiency (05 §6).
     * Label as "potential evidence coverage change", never "chance of getting the role".
     *
     * @param RequirementSufficiency[] $allRequirements full role requirement set (for denominator)
     */
    public function potentialCoverageChange(
        RequirementSufficiency $requirement,
        int $targetSufficiency,
        array $allRequirements
    ): float {
        if ($targetSufficiency < 0 || $targetSufficiency > 3) {
            throw new \InvalidArgumentException('Target sufficiency must be 0..3');
        }
        $weightSum = 0;
        foreach ($allRequirements as $r) {
            $weightSum += $r->weight();
        }
        if ($weightSum === 0) {
            return 0.0;
        }
        $delta = max(0, $targetSufficiency - $requirement->sufficiency);
        return round(100 * $requirement->weight() * $delta / (3 * $weightSum), 1);
    }
}
