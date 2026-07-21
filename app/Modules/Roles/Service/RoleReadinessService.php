<?php
namespace Continuum\Roles\Service;

use Continuum\CorePolicy\Domain\Importance;
use Continuum\CorePolicy\Domain\EvidenceLabel;
use Continuum\Evidence\Domain\EvidenceLabelPolicy;
use Continuum\Roles\Domain\RerEngine;
use Continuum\Roles\Domain\RequirementSufficiency;

/**
 * Computes RER for a candidate against a published role version by mapping each requirement to the
 * candidate's strongest CONFIRMED evidence for that requirement's skill. A taxonomy relation never
 * contributes sufficiency (D-003); it only yields a Question to Confirm.
 */
final class RoleReadinessService
{
    public function __construct(
        private $db,
        private EvidenceLabelPolicy $labels = new EvidenceLabelPolicy(),
        private RerEngine $rer = new RerEngine()
    ) {}

    /**
     * @return array{rer:float, breakdown:array, questions:array, requirements:RequirementSufficiency[]}
     */
    public function readiness(int $candidateId, int $roleVersionId): array
    {
        $requirements = $this->db->table('role_requirements')
            ->where('role_version_id', $roleVersionId)->get()->getResultArray();

        $reqSuff = [];
        $questions = [];
        foreach ($requirements as $req) {
            [$suff, $explanation] = $this->requirementSufficiency($candidateId, (int) $req['taxonomy_skill_id']);
            $importance = Importance::from($req['importance']);
            $reqSuff[] = new RequirementSufficiency($req['requirement_label'], $importance, $suff, $explanation);

            // Questions to Confirm: unmet Critical/Important, or a related-skill prompt.
            if ($importance !== Importance::Supporting && $suff < 2 && ! empty($req['question'])) {
                $questions[] = [
                    'requirement' => $req['requirement_label'],
                    'importance'  => $importance->value,
                    'question'    => $req['question'],
                    'reason'      => $suff === 0 ? 'No confirmed evidence yet' : 'Stated but not source-backed',
                ];
            }
        }

        return [
            'rer'          => $this->rer->rer($reqSuff),
            'breakdown'    => $this->rer->breakdown($reqSuff),
            'questions'    => $questions,
            'requirements' => $reqSuff,
        ];
    }

    /**
     * Strongest sufficiency for a requirement's skill from the candidate's CONFIRMED, DIRECT
     * evidence only. Related-skill graph edges are intentionally ignored for credit.
     * @return array{0:int,1:string}
     */
    private function requirementSufficiency(int $candidateId, int $skillId): array
    {
        $claims = $this->db->table('evidence_claims')
            ->where('candidate_id', $candidateId)
            ->where('taxonomy_skill_id', $skillId)
            ->get()->getResultArray();

        $best = 0;
        $explanation = 'No confirmed evidence for this requirement yet';
        foreach ($claims as $c) {
            $label = EvidenceLabel::from($c['label']);
            $confirmed = ($c['confirmation_state'] === 'confirmed');
            $hasApprovedSource = $this->db->table('evidence_links')
                ->where('claim_id', $c['id'])
                ->where('provenance', 'candidate_approved')
                ->countAllResults() > 0;
            $humanVerified = $this->db->table('evidence_links')
                ->where('claim_id', $c['id'])
                ->where('verified_by IS NOT NULL')->countAllResults() > 0;

            $suff = $this->labels->sufficiency(
                $label,
                candidateConfirmed: $confirmed,
                sourceLinkedAndApproved: $hasApprovedSource,
                humanVerified: $humanVerified
            );
            if ($suff > $best) {
                $best = $suff;
                $explanation = match ($suff) {
                    3 => 'Human-verified source-linked evidence',
                    2 => 'Candidate-approved source-linked evidence',
                    1 => 'Candidate-confirmed stated example',
                    default => 'No confirmed evidence for this requirement yet',
                };
            }
        }
        return [$best, $explanation];
    }
}
