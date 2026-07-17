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
        // leadership / people
        'treasurer'=>['budgeting'=>0.8,'stakeholder_mgmt'=>0.7], 'president'=>['leadership'=>0.8],
        'captain'=>['leadership'=>0.8], 'head '=>['leadership'=>0.7], 'club'=>['leadership'=>0.6],
        'society'=>['leadership'=>0.6], 'led'=>['leadership'=>0.7], 'lead '=>['leadership'=>0.7],
        'manage'=>['project_mgmt'=>0.7,'leadership'=>0.6], 'mentor'=>['leadership'=>0.7],
        'coordinat'=>['project_mgmt'=>0.65], 'organis'=>['project_mgmt'=>0.6],
        'volunteer'=>['community'=>0.7], 'charity'=>['community'=>0.7], 'community'=>['community'=>0.7],
        // software / engineering
        'built'=>['software'=>0.65], 'build'=>['software'=>0.6], 'develop'=>['software'=>0.65],
        'app'=>['software'=>0.7], 'website'=>['software'=>0.65], 'system'=>['software'=>0.6],
        'backend'=>['software'=>0.75], 'frontend'=>['javascript'=>0.7,'ui_ux'=>0.5],
        'microservice'=>['cloud'=>0.75,'software'=>0.6], 'docker'=>['cloud'=>0.75], 'kubernetes'=>['cloud'=>0.8],
        'cloud'=>['cloud'=>0.8], 'aws'=>['cloud'=>0.8], 'azure'=>['cloud'=>0.8], 'api'=>['api'=>0.7],
        'python'=>['python'=>0.9], 'java'=>['java'=>0.8], 'javascript'=>['javascript'=>0.85],
        'react'=>['javascript'=>0.75], 'node'=>['javascript'=>0.7],
        // data
        'sql'=>['sql'=>0.85], 'database'=>['sql'=>0.7], 'excel'=>['excel'=>0.7], 'spreadsheet'=>['excel'=>0.65],
        'dashboard'=>['dashboarding'=>0.8], 'tableau'=>['dashboarding'=>0.8], 'power bi'=>['dashboarding'=>0.8],
        'data'=>['data_analysis'=>0.7], 'analytic'=>['data_analysis'=>0.75], 'analysis'=>['data_analysis'=>0.7],
        'statistic'=>['statistics'=>0.75], 'machine learning'=>['machine_learning'=>0.85],
        'deep learning'=>['machine_learning'=>0.85], ' ml '=>['machine_learning'=>0.7], 'research'=>['research'=>0.7],
        // design
        'design'=>['design_thinking'=>0.6], 'ui/ux'=>['ui_ux'=>0.85], 'ux design'=>['ui_ux'=>0.8],
        'user experience'=>['ui_ux'=>0.8], 'user interface'=>['ui_ux'=>0.8], 'wireframe'=>['ui_ux'=>0.75],
        'figma'=>['figma'=>0.8], 'photoshop'=>['graphic_design'=>0.7],
        'illustrator'=>['graphic_design'=>0.7], 'graphic'=>['graphic_design'=>0.7],
        // business / marketing / finance
        'market'=>['marketing'=>0.8], 'seo'=>['seo'=>0.8], 'social media'=>['social_media'=>0.75],
        'instagram'=>['social_media'=>0.65], 'tiktok'=>['social_media'=>0.65], 'content'=>['content'=>0.7],
        'copywrit'=>['content'=>0.75], 'blog'=>['content'=>0.6], 'sales'=>['sales'=>0.75], 'selling'=>['sales'=>0.7],
        'customer'=>['customer_service'=>0.7], 'account'=>['accounting'=>0.75], 'bookkeep'=>['accounting'=>0.8],
        'finance'=>['finance'=>0.75], 'financial'=>['finance'=>0.7], 'audit'=>['audit'=>0.8],
        // general / transferable
        'teach'=>['teaching'=>0.7], 'tutor'=>['teaching'=>0.7], 'writing'=>['writing'=>0.7], 'author'=>['writing'=>0.6],
        'communicat'=>['communication'=>0.7], 'presentation'=>['communication'=>0.65], 'public speaking'=>['communication'=>0.75],
        'team'=>['teamwork'=>0.6], 'collaborat'=>['teamwork'=>0.65],
        'startup'=>['entrepreneurship'=>0.8], 'founder'=>['entrepreneurship'=>0.85], 'entrepreneur'=>['entrepreneurship'=>0.8],
        'innovat'=>['innovation'=>0.7], 'prototype'=>['innovation'=>0.65], 'project'=>['project_mgmt'=>0.6],
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

    /** Like inferSkills but records which keyword triggered each inferred skill (for the UI "why"). */
    public function inferSkillsExplained(string $text, array $stated = []): array
    {
        $out = []; $t = strtolower($text);
        foreach ($this->map as $kw => $skills) {
            if (str_contains($t, $kw)) {
                foreach ($skills as $code => $c) {
                    if (! isset($out[$code]) || $c > $out[$code]['confidence']) {
                        $out[$code] = ['confidence' => $c, 'source' => 'inferred', 'from' => trim($kw)];
                    }
                }
            }
        }
        foreach ($stated as $code) { $out[$code] = ['confidence' => 1.0, 'source' => 'stated', 'from' => null]; }
        return $out;
    }

    /** Build a full candidate signal from evidence text (used by Employer ranking + candidate). */
    public function signal(string $text, array $stated = [], int $verified = 0, string $domain = 'Data'): array
    {
        $skills = $this->inferSkills($text, $stated);
        $t = strtolower($text);
        $projects   = substr_count($t, 'project') + substr_count($t, 'app') + substr_count($t, 'built') + substr_count($t, 'dashboard');
        $activities = (int) (str_contains($t, 'club') || str_contains($t, 'treasurer'))
                    + (int) str_contains($t, 'volunteer')
                    + (int) (str_contains($t, 'led') || str_contains($t, 'president') || str_contains($t, 'mentor'))
                    + (int) str_contains($t, 'internship');
        return [
            'skills'     => $skills,
            'top_domain' => $domain,
            'verified'   => $verified,
            'projects'   => max(1, $projects),
            'activities' => max(1, $activities),
            'pace'       => $this->pace($t),
        ];
    }

    private function pace(string $t): string
    {
        if (preg_match('/(\\d+)\\s*\\+?\\s*year/', $t, $m) && (int) $m[1] >= 2) return 'Fast';
        if (str_contains($t, 'senior') || str_contains($t, 'manager') || str_contains($t, ' lead')) return 'Fast';
        if (str_contains($t, 'internship') || str_contains($t, 'capstone') || str_contains($t, 'industrial training') || str_contains($t, 'won ')) return 'Fast';
        if (str_contains($t, 'first-year') || str_contains($t, 'foundation') || str_contains($t, 'pre-u') || str_contains($t, 'diploma')) return 'Building';
        return 'Steady';
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

    /**
     * Holistic graduate-employability score (0-100) from a candidate signal.
     * Field-agnostic — rewards skills, verified evidence, projects, activities and experience,
     * NOT match to one specific role. Used by the University cohort dashboard.
     */
    public function employability(array $c): int
    {
        $sk    = count($c['skills'] ?? []);
        $paceB = ['Fast' => 18, 'Steady' => 12, 'Building' => 4][$c['pace'] ?? 'Steady'] ?? 12;
        return (int) min(100, 6 * $sk + 8 * ($c['verified'] ?? 0) + 9 * ($c['projects'] ?? 0) + 8 * ($c['activities'] ?? 0) + $paceB);
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
