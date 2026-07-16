<?php

namespace App\Services;

/**
 * EvidenceCheckService (Fasa B3 — Strategic B, dikemas kini C2)
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
 * Strategic C fix: when the trigger keyword equals the skill's own label
 * (e.g. "community" -> Community), "where we found it: community" reads
 * circular and unhelpful. clarifyFrom() replaces that specific case with
 * the actual evidence clause instead — real context, not an echo.
 *
 * Additive only: does not change inferSkills()/inferSkillsExplained() or
 * readiness()/match() — only adds an 'evidence_label' key and, when useful,
 * clarifies the existing 'from' key.
 */
class EvidenceCheckService
{
    private const SUPPORTED_MIN_CONF = 0.75;
    private const INFERRED_MIN_CONF  = 0.65;

    public function label(array $skills, int $verified, string $evidenceText = ''): array
    {
        foreach ($skills as $code => $s) {
            $skills[$code]['evidence_label'] = $this->labelFor($s, $verified);
            if (!empty($s['from']) && $evidenceText !== '') {
                $skills[$code]['from'] = $this->clarifyFrom($s['from'], $code, $evidenceText);
            }
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

    private function clarifyFrom(string $keyword, string $code, string $text): string
    {
        $label = \App\Libraries\Catalog::label($code);
        $normalizedKw    = strtolower(str_replace('_', ' ', trim($keyword)));
        $normalizedLabel = strtolower(str_replace('_', ' ', trim($label)));

        if ($normalizedKw !== $normalizedLabel) {
            return $keyword;
        }

        $clauses = array_filter(array_map('trim', preg_split('/[;.\n]+/', $text)));
        foreach ($clauses as $clause) {
            if (stripos($clause, $keyword) !== false) {
                $clause = trim($clause);
                return mb_strlen($clause) > 60 ? mb_substr($clause, 0, 57) . '...' : $clause;
            }
        }
        return $keyword;
    }
}