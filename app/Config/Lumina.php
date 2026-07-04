<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Lumina scoring configuration (Fasa 0).
 *
 * Semua berat pemarkahan dipusatkan di sini supaya boleh diselaraskan dengan
 * spesifikasi produk TANPA mengubah kod ScoreService secara hardcode.
 *
 * PENTING: Nilai lalai di bawah = TINGKAH LAKU SEDIA ADA (tidak mengubah apa-apa).
 * Berat "spec" (Talent Match Signal 40/25/20/10/5) disediakan sebagai rujukan
 * dan akan diaktifkan bila LearningVelocityService siap di Fasa 4.
 *
 * Guna: $cfg = config('Lumina');  atau  (new \Config\Lumina())->readiness;
 */
class Lumina extends BaseConfig
{
    /**
     * Career Readiness — berat komponen (kekal seperti kod sedia ada).
     * coverage + evidence + activity + pace = 1.00
     */
    public array $readiness = [
        'coverage' => 0.40,
        'evidence' => 0.25,
        'activity' => 0.20,
        'pace'     => 0.15,
    ];

    /**
     * Match sedia ada (ScoreService::match sekarang) — kekal supaya tiada regresi.
     * overlap + readiness + trajectory = 1.00
     */
    public array $matchLegacy = [
        'overlap'    => 0.50,
        'readiness'  => 0.30,
        'trajectory' => 0.20,
    ];

    /**
     * Talent Match Signal — sasaran spesifikasi produk.
     * Diaktifkan bila 'useSpecMatch' = true (selepas LearningVelocityService siap).
     * skill + velocity + evidence + workstyle + domain = 1.00
     */
    public array $talentMatch = [
        'skill'     => 0.40,
        'velocity'  => 0.25,
        'evidence'  => 0.20,
        'workstyle' => 0.10,
        'domain'    => 0.05,
    ];

    /** Tukar ke true di Fasa 4 untuk guna model Talent Match Signal penuh. */
    public bool $useSpecMatch = false;

    /**
     * Learning Velocity — berat komponen (Fasa 4).
     * skill_growth + project_complexity + recency + diversity + domain_progression
     */
    public array $velocity = [
        'skill_growth'       => 0.30,
        'project_complexity' => 0.25,
        'recency'            => 0.20,
        'diversity'          => 0.15,
        'domain_progression' => 0.10,
    ];

    /**
     * Band Employability / Readiness.
     * 0-49 At Risk · 50-74 Needs a Nudge · 75-100 On Track
     */
    public array $bands = [
        'at_risk'  => [0, 49],
        'nudge'    => [50, 74],
        'on_track' => [75, 100],
    ];

    public array $bandLabels = [
        'at_risk'  => 'At Risk',
        'nudge'    => 'Needs a Nudge',
        'on_track' => 'On Track',
    ];
}
