<?php

namespace App\Controllers;

use App\Services\ScoreService;

class Home extends BaseController
{
    public function index()
    {
        return view('home/landing', ['title' => 'Lumina — AI Talent Intelligence Layer']);
    }

    public function styleguide()
    {
        return view('home/styleguide', ['title' => 'Lumina · Style Guide']);
    }

    /**
     * Fasa 1 DoD: run the Aiman persona through ScoreService end-to-end.
     */
    public function selftest()
    {
        $svc = new ScoreService();

        // Aiman, 19, no resume — built from evidence text + a couple stated skills
        $evidence = 'Treasurer of the Robotics Club for 2 years; built an attendance app; led a data project.';
        $skills   = $svc->inferSkills($evidence, ['python', 'teamwork']);

        // Candidate signal (normally assembled from DB rows)
        $cand = [
            'skills'     => $skills,
            'top_domain' => 'Data',
            'verified'   => 1, 'projects' => 2, 'activities' => 3, 'pace' => 'Steady',
        ];
        // Target role: Data Analyst
        $role = ['domain' => 'Data', 'required' => ['sql', 'dashboarding', 'python', 'data_analysis']];

        $readiness = $svc->readiness($cand, $role);
        $match     = $svc->match($cand, $role);
        $whatif    = $svc->whatIf($cand, $role, ['sql', 'dashboarding']);

        $report  = "INFERRED SKILLS:\n";
        foreach ($skills as $code => $s) {
            $report .= sprintf("  - %-18s %s (%.0f%%)\n", $code, $s['source'], $s['confidence'] * 100);
        }
        $report .= "\nREADINESS (Data Analyst): {$readiness['score']}%";
        $report .= "  [coverage {$readiness['coverage']}, evidence {$readiness['evidence']}, activity {$readiness['activity']}, pace {$readiness['pace']}]\n";
        $report .= "\nMATCH: {$match['matchScore']}% ({$match['label']})";
        $report .= "  gap: " . implode(', ', $match['gap']) . "\n";
        $report .= "\nWHAT-IF (add SQL + dashboarding): {$whatif['before']}% -> {$whatif['after']}%  (+{$whatif['delta']})\n";
        $report .= "\nExpected: what-if delta is positive and readiness rises. ✓ if numbers look right.";

        return view('home/selftest', ['title' => 'Lumina · Self-test', 'report' => $report]);
    }
}
