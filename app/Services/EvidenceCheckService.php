<?php

namespace App\Services;

/**
 * EvidenceCheckService (Fasa B3 — Strategic B)
 *
 * Maps existing skill data (source: stated|inferred, confidence: 0.5-1.0
 * from ScoreService::inferSkillsExplained()) into four canonical,
 * user-facing evidence labels: Stated / Supported / Inferred / Needs Evidence.
 *
 * Conservative by design: there is no per-skill link to a specific project
 * in the current data model ('projects' is floored to 1 for every
 * candidate, so it cannot be used as a real per-skill signal). "Supported"
 * means "high-confidence inference AND the profile has verified evidence
 * (transcript)" — honest, non-overclaiming.
 *
 * Additive only: does not change inferSkills()/inferSkillsExplained() or
 * readiness()/match() — only adds an 'evidence_label' key.
 */
class EvidenceCheckService
{
    private const SUPPORTED_MIN_CONF = 0.75;
    private const INFERRED_MIN_CONF  = 0.65;

    public function label(array $skills, int $verified): array
    {
        foreach ($skills as $code => $s) {
            $skills[$code]['evidence_label'] = $this->labelFor($s, $verified);
        }
        return $skills;
    }

    private function labelFor(array $s, int $verified): string
    {
        if (($s['source'] ?? '') === 'stated') {
            return 'Stated';
        }
        $conf = (float) ($s['confidence'] ?? 0);
        if ($conf >= self::SUPPORTED_MIN_CONF && $verified === 1) {
            return 'Supported';
        }
        if ($conf >= self::INFERRED_MIN_CONF) {
            return 'Inferred';
        }
        return 'Needs Evidence';
    }
}
