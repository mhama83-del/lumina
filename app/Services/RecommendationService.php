<?php

namespace App\Services;

use App\Libraries\Catalog;

/**
 * RecommendationService (Fasa 2)
 * Menjana cadangan boleh tindak: internship roles, resume feedback,
 * next best action, micro-courses. Deterministik & explainable.
 */
class RecommendationService
{
    public function internships(string $domain): array
    {
        return [
            'Data'        => ['Data Analyst Intern','BI / Reporting Intern','Data Science Intern'],
            'Engineering' => ['Software Engineer Intern','Backend Developer Intern','Cloud / DevOps Intern'],
            'Design'      => ['UX/UI Design Intern','Product Design Intern','Design Research Intern'],
            'Business'    => ['Business Development Intern','Marketing Intern','Product / Ops Intern'],
        ][$domain] ?? ['Management Trainee Intern','Operations Intern','Project Support Intern'];
    }

    /** Micro-courses untuk setiap skill gap (kod). */
    public function microCourses(array $gapCodes): array
    {
        $map = [
            'sql'=>'SQL for Data Analysis (basics -> joins -> aggregation)',
            'python'=>'Python for Everybody / Automate the Boring Stuff',
            'dashboarding'=>'Power BI or Tableau — build 1 dashboard',
            'data_analysis'=>'Google Data Analytics fundamentals',
            'statistics'=>'Intro to Statistics (Khan / Coursera)',
            'machine_learning'=>'ML Crash Course (Google)',
            'software'=>'The Odin Project — foundations',
            'cloud'=>'AWS Cloud Practitioner essentials',
            'api'=>'REST API design basics',
            'java'=>'Java Programming fundamentals',
            'javascript'=>'JavaScript basics -> DOM -> fetch',
            'ui_ux'=>'Google UX Design certificate (intro)',
            'figma'=>'Figma for beginners — one clickable prototype',
            'communication'=>'Business communication / presentation skills',
            'leadership'=>'Lead a small project or club initiative',
            'stakeholder_mgmt'=>'Stakeholder management basics',
            'marketing'=>'Digital Marketing fundamentals (Google)',
            'finance'=>'Finance for non-finance basics',
            'accounting'=>'Bookkeeping fundamentals',
        ];
        $out = [];
        foreach (array_slice($gapCodes, 0, 4) as $c) {
            $out[] = ['skill' => Catalog::label($c), 'course' => $map[$c] ?? ('Foundations of ' . Catalog::label($c))];
        }
        return $out;
    }

    /** Resume feedback — senarai pendek, boleh tindak. */
    public function feedback(string $text, array $skills, int $readiness, array $projects, array $leadership): array
    {
        $fb = [];
        if (! preg_match('/\d/', $text)) {
            $fb[] = 'Add quantified impact — numbers, %, or results turn duties into achievements.';
        }
        if (empty($projects)) {
            $fb[] = 'Add a Projects section with 1–2 concrete builds and the tools you used.';
        } else {
            $fb[] = 'Good — projects detected. Name the tools and the outcome for each.';
        }
        if (! empty($leadership)) {
            $fb[] = 'Strong leadership evidence — tie each role to a measurable result.';
        }
        if (count($skills) < 4) {
            $fb[] = 'Only ' . count($skills) . ' skills detected — broaden your skills section with role-relevant tools.';
        }
        if (stripos($text, 'internship') === false && stripos($text, 'industrial') === false) {
            $fb[] = 'Add internship / industrial training to strengthen industry evidence.';
        }
        $fb[] = 'Lead your summary with your single strongest, role-relevant skill.';
        return array_slice($fb, 0, 4);
    }

    /** Next best action — satu ayat mengikut band. */
    public function nextAction(string $band, array $gapCodes, array $matchedCodes): string
    {
        $gap0 = $gapCodes[0] ?? null;
        $g    = $gap0 ? Catalog::label($gap0) : null;
        if ($band === 'At risk') {
            return 'Run the No-Resume guided builder to surface hidden skills, then ship one small portfolio project.';
        }
        if ($band === 'Needs a nudge') {
            return $g
                ? "Close your top gap: learn {$g} and build a small project using it within 30 days."
                : 'Add one verified project or internship to move into the On-Track band.';
        }
        $m  = $matchedCodes[0] ?? null;
        $ml = $m ? Catalog::label($m) : 'your strongest skill';
        return "You're match-ready — apply now and prepare interview stories around {$ml}.";
    }

    /**
     * Resume Coach (Fasa B4 — Strategic B). Restructures existing signals
     * into: top strengths, evidence gaps, role alignment gap, improvements
     * (reuses feedback() as-is), and one before/after example.
     */
    public function resumeCoach(
        string $text,
        array $skills,
        array $projects,
        array $leadership,
        array $bestGapCodes,
        ?string $bestRoleTitle,
        int $readiness
    ): array {
        $strengths = [];
        $gaps      = [];
        foreach ($skills as $code => $s) {
            $label = $s['evidence_label'] ?? (($s['source'] ?? '') === 'stated' ? 'Stated' : 'Inferred');
            if (in_array($label, ['Stated', 'Supported'], true)) {
                $strengths[] = Catalog::label($code);
            } elseif ($label === 'Needs Evidence') {
                $gaps[] = Catalog::label($code);
            }
        }
        $strengths = array_slice(array_unique($strengths), 0, 3);
        $gaps      = array_slice(array_unique($gaps), 0, 3);

        $roleGap = null;
        if ($bestRoleTitle && !empty($bestGapCodes)) {
            $roleGap = "To move closer to {$bestRoleTitle}, add evidence of " . Catalog::label($bestGapCodes[0]) . '.';
        }

        return [
            'top_strengths'      => $strengths,
            'evidence_gaps'      => $gaps,
            'role_alignment_gap' => $roleGap,
            'improvements'       => $this->feedback($text, $skills, $readiness, $projects, $leadership),
            'before_after'       => $this->beforeAfterExample($projects, $leadership),
        ];
    }

    /**
     * Picks the first detected project/leadership line lacking a number and
     * shows how to reframe it — without inventing a specific fake outcome.
     * Returns null if nothing suitable is found (UI hides the section).
     */
    private function beforeAfterExample(array $projects, array $leadership): ?array
    {
        foreach (array_merge($leadership, $projects) as $line) {
            $line = trim((string) $line);
            if ($line !== '' && ! preg_match('/\d/', $line)) {
                return [
                    'before' => $line,
                    'after'  => $line . ' — add a number (team size, users, %, budget, or duration) to show scale and impact.',
                ];
            }
        }
        return null;
    }
}
