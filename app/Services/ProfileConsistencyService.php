<?php

namespace App\Services;

/**
 * ProfileConsistencyService (Fasa C2 — Strategic C)
 *
 * Deterministic Profile Consistency Check. Detects patterns like leadership
 * claims without scale/outcome, or several stated/supported skills with no
 * project/activity evidence at all, or an assessment-suggested strength
 * unsupported by evidence text — NOT deception detection.
 *
 * Uses safe, non-accusatory language only ("needs more evidence", "check
 * recommended"), per canonical principle: "Lumina does not accuse
 * candidates. It shows what needs to be checked."
 *
 * Deliberately limited to patterns detectable without real risk of false
 * positives — no date-parsing, no seniority-vs-tenure comparison, no tool-
 * context checks (roadmap per the C2 spec — higher false-positive risk).
 */
class ProfileConsistencyService
{
    private const OUTCOME_WORDS = 'increas|improv|reduc|grew|deliver|achiev|launch|save|cut|boost|streamlin|automat';

    public function check(array $skills, array $projects, array $leadership, ?array $potentialProfile): array
    {
        $flags = [];

        foreach ($leadership as $clause) {
            if (! preg_match('/\d/', $clause) && ! preg_match('/' . self::OUTCOME_WORDS . '/i', $clause)) {
                $flags[] = [
                    'type'    => 'leadership_scale',
                    'message' => 'This leadership claim needs more context — add team size, budget, or outcome.',
                ];
                break;
            }
        }

        $strongCount = 0;
        foreach ($skills as $s) {
            if (in_array($s['evidence_label'] ?? '', ['Stated', 'Supported'], true)) {
                $strongCount++;
            }
        }
        if ($strongCount >= 4 && empty($projects) && empty($leadership)) {
            $flags[] = [
                'type'    => 'buzzword_gap',
                'message' => 'Several strong skills are listed, but no project or activity evidence was found — add an example.',
            ];
        }

        if ($potentialProfile && ! empty($potentialProfile['has_quiz_data'])) {
            $topStrengths = $potentialProfile['top_strengths'] ?? [];
            if (in_array('Leadership', $topStrengths, true) && empty($leadership)) {
                $flags[] = [
                    'type'    => 'assessment_gap',
                    'message' => 'Your EDGE Profile suggests Leadership as a strength — add an example to support it.',
                ];
            }
        }

        return array_slice($flags, 0, 3);
    }
}