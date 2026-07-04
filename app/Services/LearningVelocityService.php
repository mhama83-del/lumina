<?php

namespace App\Services;

/**
 * LearningVelocityService (Fasa 4)
 * Anggaran "how fast this person is growing" daripada isyarat calon.
 * Deterministik. Tiada time-series, jadi guna proxy: keluasan skill,
 * kcompleksiti projek, pace, kepelbagaian domain, progres domain.
 */
class LearningVelocityService
{
    public function velocity(array $cand): array
    {
        $skills = array_keys($cand['skills'] ?? []);
        $w = null;
        try { $w = config('Lumina')->velocity ?? null; } catch (\Throwable $e) { $w = null; }
        $w = $w ?: ['skill_growth'=>0.30,'project_complexity'=>0.25,'recency'=>0.20,'diversity'=>0.15,'domain_progression'=>0.10];

        $skillGrowth = min(100, 12 * count($skills));
        $projComplex = min(100, 25 * (int) ($cand['projects'] ?? 0));
        $recency     = ['Fast'=>90,'Steady'=>60,'Building'=>35][$cand['pace'] ?? 'Steady'] ?? 55;
        $diversity   = min(100, 20 * $this->groups($skills));
        $domainProg  = ($cand['verified'] ?? 0) ? 75 : 45;

        $score = (int) round(
            $w['skill_growth'] * $skillGrowth
          + $w['project_complexity'] * $projComplex
          + $w['recency'] * $recency
          + $w['diversity'] * $diversity
          + $w['domain_progression'] * $domainProg
        );
        $band = $score >= 70 ? 'High' : ($score >= 45 ? 'Steady' : 'Emerging');

        return [
            'score' => $score,
            'band'  => $band,
            'parts' => compact('skillGrowth','projComplex','recency','diversity','domainProg'),
        ];
    }

    /** Bilangan kumpulan skill berbeza (proxy kepelbagaian). */
    private function groups(array $codes): int
    {
        $g = [
            'data'       => ['sql','python','data_analysis','statistics','machine_learning','dashboarding','research'],
            'eng'        => ['software','cloud','java','javascript','api'],
            'design'     => ['ui_ux','figma','graphic_design','design_thinking'],
            'business'   => ['marketing','sales','content','seo','social_media','finance','accounting','audit'],
            'leadership' => ['leadership','stakeholder_mgmt','project_mgmt','budgeting','communication','teamwork','community'],
        ];
        $n = 0;
        foreach ($g as $set) { if (array_intersect($codes, $set)) $n++; }
        return max(1, $n);
    }
}
