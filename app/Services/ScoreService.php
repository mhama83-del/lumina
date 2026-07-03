<?php

namespace App\Services;

/**
 * Lumina ScoreService (Fasa 1)
 * Simulated, deterministic, explainable. No external AI calls.
 * Every method returns the score PLUS its parts so the "Why?" panel can explain it.
 */
class ScoreService
{
    /** Keyword -> inferred skills map (extend freely). */
    private array $map = [
        'treasurer'  => ['budgeting' => 0.80, 'stakeholder_mgmt' => 0.70],
        'club'       => ['leadership' => 0.70],
        'president'  => ['leadership' => 0.80],
        'led'        => ['leadership' => 0.70],
        'built'      => ['software' => 0.70],
        'app'        => ['software' => 0.70],
        'data'       => ['data_analysis' => 0.70],
        'analysis'   => ['data_analysis' => 0.70],
        'volunteer'  => ['community' => 0.70],
        'startup'    => ['entrepreneurship' => 0.75],
        'design'     => ['design_thinking' => 0.65],
    ];

    /** 6.1 Infer skills from free-text evidence + stated skills. */
    public function inferSkills(string $text, array $stated = []): array
    {
        $out = [];
        $t = strtolower($text);
        foreach ($this->map as $kw => $skills) {
            if (str_contains($t, $kw)) {
                foreach ($skills as $code => $c) {
                    $out[$code] = [
                        'confidence' => max($out[$code]['confidence'] ?? 0, $c),
                        'source'     => 'inferred',
                    ];
                }
            }
        }
        foreach ($stated as $code) {
            $out[$code] = ['confidence' => 1.0, 'source' => 'stated'];
        }
        return $out;
    }

    /** 6.2 Readiness for a target role (0-100) + sub-scores. */
    public function readiness(array $cand, array $role): array
    {
        $req     = $role['required'] ?? [];
        $have    = array_keys($cand['skills'] ?? []);
        $matched = $req ? count(array_intersect($req, $have)) : 0;

        $coverage = $req ? round(100 * $matched / count($req)) : 0;
        $evidence = min(100, 25 * ($cand['verified'] ?? 0) + 10 * ($cand['projects'] ?? 0) + 3 * count($have));
        $activity = min(100, 20 * ($cand['activities'] ?? 0));
        $pace     = ['Fast' => 80, 'Steady' => 60, 'Building' => 40][$cand['pace'] ?? 'Building'] ?? 50;

        $score = (int) round(0.40 * $coverage + 0.25 * $evidence + 0.20 * $activity + 0.15 * $pace);
        return compact('score', 'coverage', 'evidence', 'activity', 'pace');
    }

    /** 6.3 Match candidate to a role + reasons. */
    public function match(array $cand, array $role): array
    {
        $req     = $role['required'] ?? [];
        $have    = array_keys($cand['skills'] ?? []);
        $matched = array_values(array_intersect($req, $have));
        $overlap = $req ? 100 * count($matched) / count($req) : 0;
        $r       = $this->readiness($cand, $role)['score'];
        $traj    = (($role['domain'] ?? '') === ($cand['top_domain'] ?? '')) ? 100 : 70;

        $m   = (int) round(0.50 * $overlap + 0.30 * $r + 0.20 * $traj);
        $gap = array_values(array_diff($req, $have));
        $lab = $m >= 80 ? 'best' : ($m >= 65 ? 'growth' : 'stretch');

        return ['matchScore' => $m, 'matched' => $matched, 'gap' => $gap, 'label' => $lab];
    }

    /** 6.4 What-if: recompute readiness after adding skills. */
    public function whatIf(array $cand, array $role, array $add): array
    {
        $before = $this->readiness($cand, $role)['score'];
        foreach ($add as $code) {
            $cand['skills'][$code] = ['confidence' => 1.0, 'source' => 'stated'];
        }
        $after = $this->readiness($cand, $role)['score'];
        return ['before' => $before, 'after' => $after, 'delta' => $after - $before];
    }

    /** 6.5 Sub-scores (cheap, high narrative value). */
    public function industryExposure(array $c): int
    {
        return (int) min(100, 25 * ($c['internship'] ?? 0) + 20 * ($c['projects'] ?? 0) + 15 * ($c['certs'] ?? 0) + 10 * ($c['global'] ?? 0));
    }

    public function highIncome(array $c): int
    {
        return (int) min(100, 15 * ($c['high_value_skills'] ?? 0) + 20 * ($c['certs'] ?? 0) + 25 * ($c['high_income_domain'] ?? 0));
    }

    public function jobCreator(array $c): int
    {
        return (int) min(100, 20 * ($c['entrepreneur'] ?? 0) + 20 * ($c['innovation'] ?? 0) + 15 * ($c['leadership'] ?? 0) + 10 * ($c['projects'] ?? 0));
    }

    public function risk(int $readiness): string
    {
        return $readiness >= 75 ? 'On track' : ($readiness >= 50 ? 'Needs a nudge' : 'At risk');
    }

    /** 6.6 Graduate outcomes rollup (student or cohort). */
    public function outcomesIndex(array $s): int
    {
        return (int) round(
            0.30 * ($s['setara'] ?? 0) + 0.25 * ($s['readiness'] ?? 0) + 0.15 * ($s['industry'] ?? 0)
          + 0.15 * ($s['highIncome'] ?? 0) + 0.10 * ($s['jobCreator'] ?? 0) + 0.05 * ($s['esg'] ?? 0)
        );
    }
}
